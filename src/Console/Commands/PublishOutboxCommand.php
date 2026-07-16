<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Console\Commands;

use Hekal\EventOutbox\Services\OutboxPublisher;
use Illuminate\Console\Command;

final class PublishOutboxCommand extends Command
{
    protected $signature = 'outbox:publish {--limit=}';

    protected $description = 'Publish pending transactional outbox messages';

    public function handle(OutboxPublisher $publisher): int
    {
        $limit = $this->option('limit');
        $count = $publisher->publishBatch($limit !== null ? (int) $limit : null);
        $this->info("Published {$count} outbox message(s).");

        return self::SUCCESS;
    }
}
