<?php

namespace App\Application\Middleware\Auth;

enum WebhookScope: string
{
    case MemberBirthday = 'MemberBirthday';
    case Group = 'Group';
}
