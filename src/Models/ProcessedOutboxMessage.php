<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $message_uuid
 * @property string $consumer
 * @property Carbon $processed_at
 */
class ProcessedOutboxMessage extends Model
{
    public $timestamps = false;

    protected $table = 'outbox_processed_messages';

    protected $fillable = [
        'message_uuid',
        'consumer',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'processed_at' => 'datetime',
        ];
    }
}
