<?php

declare(strict_types=1);

return [
    'table' => 'outbox_messages',
    'processed_table' => 'outbox_processed_messages',

    /*
    |--------------------------------------------------------------------------
    | Require an open DB transaction when recording
    |--------------------------------------------------------------------------
    */
    'require_transaction' => env('OUTBOX_REQUIRE_TRANSACTION', true),

    'publish' => [
        'batch_size' => (int) env('OUTBOX_BATCH_SIZE', 50),
        'max_attempts' => (int) env('OUTBOX_MAX_ATTEMPTS', 10),
        'backoff_seconds' => (int) env('OUTBOX_BACKOFF', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Map outbox type => Laravel event class
    |--------------------------------------------------------------------------
    |
    | 'order.created' => App\Events\OrderCreated::class,
    |
    | Event constructor should accept array $payload (and optional headers).
    |
    */
    'event_map' => [
        //
    ],

    'retention_days' => (int) env('OUTBOX_RETENTION_DAYS', 14),
];
