<?php

declare(strict_types=1);

namespace Hekal\EventOutbox\Exceptions;

use RuntimeException;

class OutboxException extends RuntimeException
{
    public static function transactionRequired(): self
    {
        return new self('Outbox::record() must be called inside an open database transaction.');
    }

    public static function unmappedType(string $type): self
    {
        return new self("No event mapping configured for outbox type [{$type}].");
    }
}
