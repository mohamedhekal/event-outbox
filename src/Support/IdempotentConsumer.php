<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Support;

use Hekal\EventOutbox\Models\ProcessedOutboxMessage;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Idempotent consumer helper: run a callback at most once per message UUID + consumer name.
 */
final class IdempotentConsumer
{
    /**
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T|null Null when already processed
     */
    public function once(string $messageUuid, callable $callback, string $consumer = 'default'): mixed
    {
        if ($this->wasProcessed($messageUuid, $consumer)) {
            return null;
        }

        try {
            return DB::transaction(function () use ($messageUuid, $callback, $consumer) {
                if ($this->wasProcessed($messageUuid, $consumer)) {
                    return null;
                }

                ProcessedOutboxMessage::query()->create([
                    'message_uuid' => $messageUuid,
                    'consumer' => $consumer,
                    'processed_at' => now(),
                ]);

                return $callback();
            });
        } catch (Throwable $e) {
            // Concurrent insert of the same UUID/consumer — treat as already processed.
            if ($this->isDuplicateKeyException($e) && $this->wasProcessed($messageUuid, $consumer)) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * @phpstan-impure
     */
    public function wasProcessed(string $messageUuid, string $consumer = 'default'): bool
    {
        return ProcessedOutboxMessage::query()
            ->where('message_uuid', $messageUuid)
            ->where('consumer', $consumer)
            ->exists();
    }

    private function isDuplicateKeyException(Throwable $e): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'unique')
            || str_contains($message, 'duplicate');
    }
}
