<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Services;

use Hekal\EventOutbox\Enums\OutboxStatus;
use Hekal\EventOutbox\Exceptions\OutboxException;
use Hekal\EventOutbox\Models\OutboxMessage;
use Illuminate\Support\Facades\DB;

final class OutboxRecorder
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $headers
     */
    public function record(string $type, array $payload, array $headers = []): OutboxMessage
    {
        if (config('outbox.require_transaction', true) && DB::transactionLevel() < 1) {
            throw OutboxException::transactionRequired();
        }

        return OutboxMessage::query()->create([
            'type' => $type,
            'payload' => $payload,
            'headers' => $headers === [] ? null : $headers,
            'status' => OutboxStatus::Pending->value,
            'available_at' => now(),
        ]);
    }
}
