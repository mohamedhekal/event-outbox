<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Tests;

use Hekal\EventOutbox\EventOutboxServiceProvider;
use Hekal\EventOutbox\Tests\Fixtures\OrderCreated;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [EventOutboxServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('outbox.require_transaction', true);
        $app['config']->set('outbox.publish.batch_size', 50);
        $app['config']->set('outbox.publish.max_attempts', 3);
        $app['config']->set('outbox.publish.backoff_seconds', 0);
        $app['config']->set('outbox.event_map', [
            'order.created' => OrderCreated::class,
        ]);
        $app['config']->set('outbox.retention_days', 7);
    }
}
