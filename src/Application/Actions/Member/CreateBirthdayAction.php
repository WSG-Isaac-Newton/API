<?php

namespace App\Application\Actions\Member;

use App\Application\Actions\Action;
use App\Domain\Congressus\Member;
use Psr\Http\Message\ResponseInterface as Response;

final class CreateBirthdayAction extends Action
{
    protected function action(): Response
    {
        $member = Member::fromRequest($this->request);

        if (!$member->mayShowBirthday || $member->isDeleted || $member->isArchived) {
            return $this->respondCreated();
        }

        if (!$member->memberId || !$member->dateOfBirth) {
            throw new \InvalidArgumentException('Missing required fields: member_id and date_of_birth');
        }

        $stmt = $this->db->prepare("INSERT IGNORE INTO birthdays (congressus_member_id, date_of_birth) VALUES (:memberId, :dateOfBirth)");
        $stmt->execute([
            ':memberId' => $member->memberId,
            ':dateOfBirth' => $member->dateOfBirth,
        ]);

        $this->logger->info("Birthday for member ID {$member->memberId} was created.");
        return $this->respondCreated();
    }
}
