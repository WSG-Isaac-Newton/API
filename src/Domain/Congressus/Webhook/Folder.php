<?php

namespace App\Domain\Congressus\Webhook;

final readonly class Folder
{
    public function __construct(
        public readonly int     $id,
        public readonly ?int    $parent_id,
        public readonly string  $name,
        public readonly string  $slug,
        public readonly string  $path,
        public readonly string  $breadcrumbs,
        public readonly bool    $published,
        public readonly string  $order_type,
    ) {}
}
