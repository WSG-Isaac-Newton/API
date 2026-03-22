<?php

namespace App\Application\Actions\Group;

use App\Application\Actions\Action;
use App\Domain\Congressus\Webhook\Group;
use App\Domain\Congressus\Webhook\Trigger;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

final class GroupAction extends Action
{
    protected function action(): Response
    {
        match ($this->getTrigger()) {
            Trigger::GroupAdded, Trigger::GroupUpdated => $this->queueGroupEvent(),
            Trigger::GroupMembershipAdded, Trigger::GroupMembershipUpdated => $this->queueGroupMembershipEvent(),
            Trigger::GroupDeleted, Trigger::GroupMembershipDeleted => $this->ignore(), // ignore until Congressus fixes the schema
            default => throw new HttpBadRequestException($this->request, "Unsupported trigger"),
        };

        return $this->respondOk();
    }

    protected function ignore(): void {}

    protected function queueGroupEvent(): void
    {
        $trigger = $this->getTrigger();
        $group = $this->getGroup();

        $stmt = $this->db->prepare(
            "INSERT INTO congressus_webhooks_event_queue (`event_trigger`, `group_id`, `group_name`, `group_breadcrumbs`) VALUES (:trigger, :groupId, :groupName, :groupBreadcrumbs);"
        );
        $stmt->execute([
            ':trigger' => $trigger->value,
            ':groupId' => $group->id,
            ':groupName' => $group->name,
            ':groupBreadcrumbs' => $group->getBreadcrumbs(),
        ]);

        $this->logger->info("Queued {$trigger->value} for group {$group->id}");
    }

    protected function queueGroupMembershipEvent(): void
    {
        $trigger = $this->getTrigger();
        $group = $this->getGroup();
        $memberId = $this->getMemberId();

        $stmt = $this->db->prepare(
            "INSERT INTO congressus_webhooks_event_queue (`event_trigger`, `group_id`, `group_name`, `group_breadcrumbs`,`member_id`) VALUES (:trigger, :groupId, :groupName, :groupBreadcrumbs, :memberId);"
        );
        $stmt->execute([
            ':trigger' => $trigger->value,
            ':groupId' => $group->id,
            ':groupName' => $group->name,
            ':groupBreadcrumbs' => $group->getBreadcrumbs(),
            ':memberId' => $memberId,
        ]);

        $this->logger->info("Queued {$trigger->value} for group {$group->id}");
    }

    protected function getTrigger(): Trigger
    {
        $body = $this->request->getParsedBody();

        if (!isset($body['webhook_event_trigger'])) {
            throw new HttpBadRequestException($this->request, "Trigger is required");
        }

        try {
            return Trigger::from($body['webhook_event_trigger']);
        } catch (\ValueError $e) {
            throw new HttpBadRequestException($this->request, "Invalid trigger value: {$body['webhook_event_trigger']}");
        }
    }

    protected function getGroup(): Group
    {
        try {
            return Group::fromRequest($this->request);
        } catch (\Throwable $th) {
            throw new HttpBadRequestException($this->request, "Invalid group data: " . $th->getMessage());
        }
    }

    protected function getMemberId(): int
    {
        if (isset($this->getFormData()['data']['collection_membership']['member_id'])) {
            return $this->getFormData()['data']['collection_membership']['member_id'];
        }
        throw new HttpBadRequestException($this->request, "Member ID is required");
    }
}
