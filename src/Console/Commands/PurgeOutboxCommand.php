<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Console\Commands;

use Hekal\EventOutbox\Enums\OutboxStatus;
use Hekal\EventOutbox\Models\OutboxMessage;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

final class PurgeOutboxCommand extends Command
{
    protected $signature = 'outbox:purge {--days=} {--dry-run}';

    protected $description = 'Purge published outbox messages older than retention';

    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('outbox.retention_days', 14));
        $cutoff = Carbon::now()->subDays(max(1, $days));

        $query = OutboxMessage::query()
            ->where('status', OutboxStatus::Published->value)
            ->where('published_at', '<', $cutoff);

        $count = $query->count();

        if ($this->option('dry-run')) {
            $this->info("Would delete {$count} published message(s).");

            return self::SUCCESS;
        }

        $deleted = $query->delete();
        $this->info("Deleted {$deleted} published message(s).");

        return self::SUCCESS;
    }
}
