<?php

namespace App\Domain\Congressus\Webhook;

enum Trigger: string
{
    case GroupAdded             = 'group_added';
    case GroupUpdated           = 'group_updated';
    case GroupDeleted           = 'group_deleted';
    case GroupMembershipAdded   = 'group_membership_added';
    case GroupMembershipUpdated = 'group_membership_updated';
    case GroupMembershipDeleted = 'group_membership_deleted';
}
