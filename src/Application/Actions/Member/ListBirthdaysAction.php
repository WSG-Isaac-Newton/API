<?php

namespace App\Application\Actions\Member;

use App\Application\Actions\Action;
use Psr\Http\Message\ResponseInterface as Response;

final class ListBirthdaysAction extends Action
{
    protected function action(): Response
    {
        $this->logger->info("Birthday list was viewed.");

        $this->deleteOldBirthdays();

        $stmt = $this->db->query(
            "SELECT congressus_member_id FROM birthdays 
            WHERE DATE_FORMAT(date_of_birth, '%m-%d') = DATE_FORMAT(NOW(), '%m-%d');"
        );
        $birthdays = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        return $this->respondWithData($birthdays);
    }

    private function deleteOldBirthdays(): void
    {
        $this->db->query(
            "DELETE FROM birthdays
            WHERE DATE_FORMAT(date_of_birth, '%m-%d') != DATE_FORMAT(NOW(), '%m-%d');"
        );
    }
}
