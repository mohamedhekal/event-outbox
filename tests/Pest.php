<?php

declare(strict_types=1);

use Hekal\EventOutbox\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;

pest()->extend(TestCase::class)
    ->use(DatabaseMigrations::class)
    ->in(__DIR__);
