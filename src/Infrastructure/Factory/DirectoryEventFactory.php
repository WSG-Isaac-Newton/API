<?php

namespace App\Infrastructure\Factory;

use App\Domain\Congressus\Webhook\Group;
use App\Domain\Congressus\Webhook\Trigger;
use App\Domain\Directory\Event;
use App\Domain\Directory\GroupEvent;

final class DirectoryEventFactory
{
    public static function createFromCongressusGroupWebhook(Trigger $trigger, Group $group, ?int $memberId = null): Event
    {
        return new GroupEvent(
            id: null,
            trigger: $trigger,
            groupId: $group->id,
            groupName: $group->name,
            groupBreadcrumbs: $group->getBreadcrumbs(),
            memberId: $memberId
        );
    }

    public static function createFromDatabaseRecord(array $record): Event
    {
        return new GroupEvent(
            id: (int)$record['id'],
            trigger: Trigger::from($record['event_trigger']),
            groupId: (int)$record['group_id'],
            groupName: $record['group_name'],
            groupBreadcrumbs: $record['group_breadcrumbs'],
            memberId: isset($record['member_id']) ? (int)$record['member_id'] : null
        );
    }
}
