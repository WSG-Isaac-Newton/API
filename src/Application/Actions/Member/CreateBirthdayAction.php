<?php

declare(strict_types=1);

namespace App\Application\Actions\Member;

use App\Application\Actions\Action;
use App\Domain\Congressus\Webhook\Member;
use App\Domain\Member\Repository\BirthdayRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class CreateBirthdayAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private readonly BirthdayRepository $birthdayRepository
    ) {
        parent::__construct($logger);
    }
    
    protected function action(): Response
    {
        // Respond to the webhook call (from Congressus)
        $member = Member::fromRequest($this->request);

        if (!$member->mayShowBirthday || $member->isDeleted || $member->isArchived) {
            return $this->respondOk();
        }

        if (!$member->memberId || !$member->dateOfBirth) {
            $this->logger->warning("Failed to create birthday: Missing required fields in webhook payload.");
            return $this->respondWithData(['error' => 'Missing required fields: member_id and date_of_birth'], 400);
        }

        // Create a new birthday entry in the repository
        try {
            $this->birthdayRepository->createBirthday($member->memberId, $member->dateOfBirth);
            $this->logger->info("Birthday for member ID {$member->memberId} was created.");
            return $this->respondOk();
        } catch (\Exception $e) {
            $this->logger->error("Failed to create birthday for member ID {$member->memberId}: " . $e->getMessage());
            return $this->respondWithData(['error' => 'Failed to create birthday'], 500);
        }
    }
}
