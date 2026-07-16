<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Facades;

use Hekal\EventOutbox\Models\OutboxMessage;
use Hekal\EventOutbox\Services\OutboxRecorder;
use Illuminate\Support\Facades\Facade;

/**
 * @method static OutboxMessage record(string $type, array<string, mixed> $payload, array<string, mixed> $headers = [])
 *
 * @see OutboxRecorder
 */
final class Outbox extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return OutboxRecorder::class;
    }
}
