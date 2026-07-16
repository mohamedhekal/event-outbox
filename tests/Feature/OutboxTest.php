<?php

declare(strict_types=1);

use Hekal\EventOutbox\Enums\OutboxStatus;
use Hekal\EventOutbox\Events\OutboxMessagePublished;
use Hekal\EventOutbox\Exceptions\OutboxException;
use Hekal\EventOutbox\Facades\Outbox;
use Hekal\EventOutbox\Models\OutboxMessage;
use Hekal\EventOutbox\Services\OutboxPublisher;
use Hekal\EventOutbox\Support\IdempotentConsumer;
use Hekal\EventOutbox\Tests\Fixtures\OrderCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

it('requires an open transaction when configured', function () {
    expect(fn () => Outbox::record('order.created', ['id' => 1]))
        ->toThrow(OutboxException::class);
});

it('records messages inside a transaction and publishes them', function () {
    Event::fake([OutboxMessagePublished::class, OrderCreated::class]);

    DB::transaction(function () {
        Outbox::record('order.created', ['id' => 42], ['source' => 'test']);
    });

    expect(OutboxMessage::query()->count())->toBe(1)
        ->and(OutboxMessage::query()->first()?->status)->toBe(OutboxStatus::Pending->value);

    $published = app(OutboxPublisher::class)->publishBatch();

    expect($published)->toBe(1)
        ->and(OutboxMessage::query()->first()?->status)->toBe(OutboxStatus::Published->value);

    Event::assertDispatched(OrderCreated::class, function (OrderCreated $event) {
        return ($event->payload['id'] ?? null) === 42;
    });
    Event::assertDispatched(OutboxMessagePublished::class);
});

it('runs idempotent consumers only once', function () {
    $consumer = app(IdempotentConsumer::class);
    $runs = 0;

    $first = $consumer->once('msg-1', function () use (&$runs) {
        $runs++;

        return 'ok';
    });

    $second = $consumer->once('msg-1', function () use (&$runs) {
        $runs++;

        return 'again';
    });

    expect($first)->toBe('ok')
        ->and($second)->toBeNull()
        ->and($runs)->toBe(1)
        ->and($consumer->wasProcessed('msg-1'))->toBeTrue();
});

it('purges old published messages', function () {
    DB::transaction(function () {
        Outbox::record('order.created', ['id' => 1]);
    });

    app(OutboxPublisher::class)->publishBatch();

    OutboxMessage::query()->update([
        'published_at' => now()->subDays(30),
    ]);

    $this->artisan('outbox:purge', ['--days' => 7])->assertSuccessful();

    expect(OutboxMessage::query()->count())->toBe(0);
});
