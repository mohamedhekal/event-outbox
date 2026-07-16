<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Tests\Fixtures;

final class OrderCreated
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $headers
     */
    public function __construct(
        public readonly array $payload,
        public readonly array $headers = [],
    ) {}
}
