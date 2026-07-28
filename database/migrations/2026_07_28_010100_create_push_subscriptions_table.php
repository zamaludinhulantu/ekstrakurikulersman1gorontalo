<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('push_subscriptions')) {
            return;
        }

        Schema::create('push_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('endpoint');
            $table->string('public_key', 255);
            $table->string('auth_token', 255);
            $table->string('content_encoding', 32)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('device_name', 120)->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'endpoint'], 'push_subscriptions_user_endpoint_unique');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('push_subscriptions')) {
            Schema::drop('push_subscriptions');
        }
    }
};
