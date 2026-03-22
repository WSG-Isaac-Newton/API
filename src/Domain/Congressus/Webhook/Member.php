<?php

namespace App\Domain\Congressus\Webhook;

use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class Member implements ParseableFromRequest
{
    public function __construct(
        public int     $memberId,
        public string  $firstName,
        public string  $lastName,
        public string  $email,
        public string  $dateOfBirth,
        public bool    $isDeleted,
        public bool    $isLocked,
        public bool    $isArchived,
        public bool    $mayShowBirthday,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $body = $request->getParsedBody();
        $member = $body['data']['member'];

        return new self(
            memberId:               $member['id'],
            firstName:              $member['first_name'],
            lastName:               $member['last_name'],
            email:                  $member['email'],
            dateOfBirth:            $member['date_of_birth'],
            isDeleted:              $member['deleted'],
            isLocked:               $member['locked'],
            isArchived:             $member['status']['archived'],
            mayShowBirthday:        $member["custom_field_data"]["field_preference_birthday"] ?? false,
        );
    }
}
