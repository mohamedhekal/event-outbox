<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Models;

use Hekal\EventOutbox\Enums\OutboxStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $uuid
 * @property string $type
 * @property array<string, mixed> $payload
 * @property array<string, mixed>|null $headers
 * @property string $status
 * @property int $attempts
 * @property Carbon|null $available_at
 * @property Carbon|null $published_at
 * @property string|null $last_error
 */
class OutboxMessage extends Model
{
    protected $table = 'outbox_messages';

    protected $fillable = [
        'uuid',
        'type',
        'payload',
        'headers',
        'status',
        'attempts',
        'available_at',
        'published_at',
        'last_error',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'headers' => 'array',
            'attempts' => 'integer',
            'available_at' => 'datetime',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $message): void {
            $message->uuid ??= (string) Str::uuid();
            $message->status ??= OutboxStatus::Pending->value;
            $message->available_at ??= Carbon::now();
        });
    }

    public function markPublishing(): void
    {
        $this->forceFill([
            'status' => OutboxStatus::Publishing->value,
            'attempts' => $this->attempts + 1,
        ])->save();
    }

    public function markPublished(): void
    {
        $this->forceFill([
            'status' => OutboxStatus::Published->value,
            'published_at' => now(),
            'last_error' => null,
            'available_at' => null,
        ])->save();
    }

    public function markFailed(string $error, int $backoffSeconds, int $maxAttempts): void
    {
        if ($this->attempts >= $maxAttempts) {
            $this->forceFill([
                'status' => OutboxStatus::Failed->value,
                'last_error' => $error,
                'available_at' => null,
            ])->save();

            return;
        }

        $this->forceFill([
            'status' => OutboxStatus::Pending->value,
            'last_error' => $error,
            'available_at' => now()->addSeconds($backoffSeconds),
        ])->save();
    }
}
