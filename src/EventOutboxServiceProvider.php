<?php

declare(strict_types=1);

namespace Hekal\EventOutbox;

use Hekal\EventOutbox\Console\Commands\PublishOutboxCommand;
use Hekal\EventOutbox\Console\Commands\PurgeOutboxCommand;
use Hekal\EventOutbox\Services\OutboxPublisher;
use Hekal\EventOutbox\Services\OutboxRecorder;
use Hekal\EventOutbox\Support\IdempotentConsumer;
use Illuminate\Support\ServiceProvider;

final class EventOutboxServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/outbox.php', 'outbox');

        $this->app->singleton(OutboxRecorder::class);
        $this->app->singleton(OutboxPublisher::class);
        $this->app->singleton(IdempotentConsumer::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/outbox.php' => config_path('outbox.php'),
        ], 'outbox-config');

        $this->publishes([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ], 'outbox-migrations');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                PublishOutboxCommand::class,
                PurgeOutboxCommand::class,
            ]);
        }
    }
}
