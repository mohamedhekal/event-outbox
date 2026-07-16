<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Enums;

enum OutboxStatus: string
{
    case Pending = 'pending';
    case Publishing = 'publishing';
    case Published = 'published';
    case Failed = 'failed';
}
