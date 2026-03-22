<?php

namespace App\Domain\Congressus\Webhook;

use Psr\Http\Message\ServerRequestInterface as Request;

final readonly class Group implements ParseableFromRequest
{
    public function __construct(
        public readonly int             $id,
        public readonly Folder          $folder,
        public readonly string          $name,
        public readonly ?string         $description_short,
        public readonly string          $slug,
        public readonly string          $path,
        public readonly bool            $published,
        public readonly string          $start,
        public readonly ?string         $end,
        public readonly ?string         $memo,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $body = $request->getParsedBody();

        if (isset($body['data']['group'])) {
            $group = $body['data']['group'];
        } else if (isset($body['data']['collection_membership']['group'])) {
            $group = $body['data']['collection_membership']['group'];
        } else {
            throw new \InvalidArgumentException('Group data not found in request');
        }

        return new self(
            id: $group['id'] ?? null,
            folder: new Folder(...$group['folder'] ?? null) ?? null,
            name: $group['name'] ?? null,
            description_short: $group['description_short'] ?? null,
            slug: $group['slug'] ?? null,
            path: $group['path'] ?? null,
            published: $group['published'] ?? null,
            start: $group['start'] ?? null,
            end: $group['end'] ?? null,
            memo: $group['memo'] ?? null,
        );
    }

    public function getBreadcrumbs(): string
    {
        return $this->folder->breadcrumbs;
    }
}
