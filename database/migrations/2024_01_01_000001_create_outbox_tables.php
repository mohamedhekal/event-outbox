<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbox_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('type');
            $table->json('payload');
            $table->json('headers')->nullable();
            $table->string('status', 32)->default('pending'); // pending|publishing|published|failed
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->timestamp('available_at')->nullable()->index();
            $table->timestamp('published_at')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['status', 'available_at', 'id']);
            $table->index(['type', 'created_at']);
        });

        Schema::create('outbox_processed_messages', function (Blueprint $table) {
            $table->id();
            $table->uuid('message_uuid');
            $table->string('consumer', 100)->default('default');
            $table->timestamp('processed_at');

            $table->unique(['message_uuid', 'consumer']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_processed_messages');
        Schema::dropIfExists('outbox_messages');
    }
};
