<?php

declare(strict_types=1);

namespace App\Application\Actions\Member;

use App\Application\Actions\Action;
use App\Domain\Member\Repository\BirthdayRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class ListBirthdaysAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private readonly BirthdayRepository $birthdayRepository
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        try {
            $this->birthdayRepository->deleteOldBirthdays();
        } catch (\PDOException $e) {
            $this->logger->error("Failed to delete old birthdays: " . $e->getMessage());
            return $this->respondWithData(['error' => 'Failed to delete old birthdays'], 500);
        }

        try {
            $birthdays = $this->birthdayRepository->getTodaysBirthdays();
            $this->logger->info("Birthday list was viewed.");
        } catch (\PDOException $e) {
            $this->logger->error("Failed to retrieve today's birthdays: " . $e->getMessage());
            return $this->respondWithData(['error' => "Failed to retrieve today's birthdays"], 500);
        }

        return $this->respondWithData($birthdays);
    }
}
