<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Events;

use Hekal\EventOutbox\Models\OutboxMessage;
use Illuminate\Foundation\Events\Dispatchable;

final class OutboxMessagePublished
{
    use Dispatchable;

    public function __construct(
        public readonly OutboxMessage $message,
    ) {}
}
