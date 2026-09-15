<?php

declare(strict_types=1);

namespace App\Domain\Member\Repository;

final class BirthdayRepository
{
    public function __construct(
        private readonly \PDO $db
    ) {}

    public function createBirthday(int $memberId, string $dateOfBirth): void
    {
        $stmt = $this->db->prepare("INSERT IGNORE INTO birthdays (congressus_member_id, date_of_birth) VALUES (:memberId, :dateOfBirth)");
        $stmt->execute([
            ':memberId' => $memberId,
            ':dateOfBirth' => $dateOfBirth,
        ]);
    }

    public function getTodaysBirthdays(): array
    {
        $stmt = $this->db->query(
            "SELECT congressus_member_id FROM birthdays 
            WHERE DATE_FORMAT(date_of_birth, '%m-%d') = DATE_FORMAT(NOW(), '%m-%d');"
        );
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function deleteOldBirthdays(): void
    {
        $this->db->query(
            "DELETE FROM birthdays
            WHERE DATE_FORMAT(date_of_birth, '%m-%d') != DATE_FORMAT(NOW(), '%m-%d');"
        );
    }
}
