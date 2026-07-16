<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Services;

use Hekal\EventOutbox\Enums\OutboxStatus;
use Hekal\EventOutbox\Events\OutboxMessagePublished;
use Hekal\EventOutbox\Exceptions\OutboxException;
use Hekal\EventOutbox\Models\OutboxMessage;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Throwable;

final class OutboxPublisher
{
    public function publishBatch(?int $limit = null): int
    {
        $limit ??= (int) config('outbox.publish.batch_size', 50);
        $published = 0;

        $messages = $this->claim($limit);

        foreach ($messages as $message) {
            if ($this->publishOne($message)) {
                $published++;
            }
        }

        return $published;
    }

    public function publishOne(OutboxMessage $message): bool
    {
        $maxAttempts = (int) config('outbox.publish.max_attempts', 10);
        $backoff = (int) config('outbox.publish.backoff_seconds', 30);

        try {
            $this->dispatchMappedEvent($message);
            event(new OutboxMessagePublished($message));
            $message->markPublished();

            return true;
        } catch (Throwable $e) {
            $message->markFailed($e->getMessage(), $backoff, $maxAttempts);

            return false;
        }
    }

    /**
     * @return list<OutboxMessage>
     */
    private function claim(int $limit): array
    {
        return DB::transaction(function () use ($limit) {
            $query = OutboxMessage::query()
                ->where('status', OutboxStatus::Pending->value)
                ->where(function ($q) {
                    $q->whereNull('available_at')
                        ->orWhere('available_at', '<=', now());
                })
                ->orderBy('id')
                ->limit($limit);

            // SQLite does not support lockForUpdate skip; still safe in tests.
            if (DB::getDriverName() !== 'sqlite') {
                $query->lockForUpdate();
            }

            /** @var list<OutboxMessage> $messages */
            $messages = $query->get()->all();

            foreach ($messages as $message) {
                $message->markPublishing();
            }

            return $messages;
        });
    }

    private function dispatchMappedEvent(OutboxMessage $message): void
    {
        /** @var array<string, class-string> $map */
        $map = (array) config('outbox.event_map', []);

        if (! isset($map[$message->type])) {
            // Still considered published via OutboxMessagePublished only.
            return;
        }

        $class = $map[$message->type];

        if (! class_exists($class)) {
            throw OutboxException::unmappedType($message->type);
        }

        $event = $this->instantiateEvent($class, $message);
        event($event);
    }

    /**
     * @param  class-string  $class
     */
    private function instantiateEvent(string $class, OutboxMessage $message): object
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class;
        }

        $params = $constructor->getParameters();
        $args = [];

        foreach ($params as $param) {
            $name = $param->getName();
            $args[] = match ($name) {
                'payload', 'data' => $message->payload,
                'headers' => $message->headers ?? [],
                'message', 'outboxMessage' => $message,
                'type' => $message->type,
                'uuid' => $message->uuid,
                default => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
            };
        }

        return $reflection->newInstanceArgs($args);
    }
}
