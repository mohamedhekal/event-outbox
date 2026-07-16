# Event Outbox


[![CI](https://github.com/mohamedmohamedhekal/event-outbox/actions/workflows/tests.yml/badge.svg)](https://github.com/mohamedmohamedhekal/event-outbox/actions)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4.svg)](https://www.php.net/)
[![Laravel](https://img.shields.io/badge/Laravel-11%2F12-FF2D20.svg)](https://laravel.com/)

**Search terms:** laravel, outbox, domain-events, reliability, messaging, php, laravel-package, transactional-outbox, events, queues.


Transactional outbox for Laravel: persist domain events in the same DB transaction as your business write, then publish them reliably with a worker.

## Problem

`DB::commit()` + `event()` / `Queue::push()` is a dual-write. If the process dies between them, state and events diverge. The outbox pattern makes event emission part of the transaction.

## Installation

```bash
composer require mohamedhekal/event-outbox
php artisan vendor:publish --tag=outbox-config
php artisan migrate
```

## Usage

```php
use Hekal\EventOutbox\Facades\Outbox;
use Illuminate\Support\Facades\DB;

DB::transaction(function () use ($order) {
    $order->save();

    Outbox::record('order.created', [
        'id' => $order->id,
        'total' => $order->total,
    ]);
});
```

Schedule the publisher:

```php
// routes/console.php or Kernel
Schedule::command('outbox:publish')->everyMinute();
```

Map types to Laravel events in `config/outbox.php`:

```php
'event_map' => [
    'order.created' => App\Events\OrderCreated::class,
],
```

`OrderCreated` should accept `array $payload` (and optionally `array $headers`).

Every successful publish also fires `OutboxMessagePublished`.

## Idempotent consumers

```php
use Hekal\EventOutbox\Support\IdempotentConsumer;

app(IdempotentConsumer::class)->once($message->uuid, function () {
    // side effects
}, consumer: 'billing');
```

## Commands

```bash
php artisan outbox:publish --limit=100
php artisan outbox:purge --days=14
```

## Testing

```bash
composer install && composer test
```

## License

MIT
