<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Poll;

use App\Domain\Poll\Poll;
use App\Domain\Poll\PollRepositoryInterface;
use App\Domain\Poll\VoteRepositoryInterface;
use Cycle\Database\DatabaseInterface;
use Cycle\Database\Query\SelectQuery;
use Cycle\ORM\EntityManager;
use Cycle\ORM\EntityManagerInterface;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\RepositoryInterface;
use DateTimeImmutable;

final class PollRepository implements PollRepositoryInterface
{
    private readonly SelectQuery $selectFromPolls;

    /** @var \Cycle\ORM\Select\Repository $repository */
    private readonly RepositoryInterface $repository;
    
    private readonly EntityManagerInterface $em;
    
    public function __construct(
        private readonly DatabaseInterface $database,
        private readonly ORMInterface $orm,
        private readonly VoteRepositoryInterface $voteRepository,
    ) {
        $this->selectFromPolls = $this->database->select()->from('polls');
        $this->repository = $this->orm->getRepository(Poll::class);
        $this->em = new EntityManager($this->orm);
    }

    #[\Override]
    public function getAllPollIds(): array
    {
        return $this->selectFromPolls->columns('id')->fetchAll();
    }

    #[\Override]
    public function getAllActivePollIds(): array
    {
        return $this->selectFromPolls
            ->columns('id')
            ->where('expires_at', null)
            ->orWhere('expires_at', '>', new DateTimeImmutable())
            ->fetchAll();
    }

    #[\Override]
    public function getAllExpiredPollIds(): array
    {
        return $this->selectFromPolls
            ->columns('id')
            ->where('expires_at', '<', new DateTimeImmutable())
            ->fetchAll();
    }

    #[\Override]
    public function getPollById(int $id): ?Poll
    {
        $poll = $this->repository->findByPK($id);
        $poll?->setVoteCounts($this->voteRepository->countByPollId($id));
        return $poll;
    }

    #[\Override]
    public function createPoll(Poll $poll): void
    {
        $this->em->persist($poll);
        $this->em->run();
    }

    #[\Override]
    public function updatePoll(Poll $poll): void
    {
        $this->em->persist($poll);
        $this->em->run();
    }

    #[\Override]
    public function deletePoll(int $id): void
    {
        $poll = $this->getPollById($id);
        if ($poll !== null) {
            $this->em->delete($poll);
            $this->em->run();
        }
    }
}
