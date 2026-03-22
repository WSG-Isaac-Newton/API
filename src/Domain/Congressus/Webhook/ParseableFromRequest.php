<?php

namespace App\Domain\Congressus\Webhook;

use Psr\Http\Message\ServerRequestInterface as Request;

interface ParseableFromRequest
{
    public static function fromRequest(Request $request): self;
}
