<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumns('schedules', ['reminder_sent_day_before_at', 'reminder_sent_hours_before_at'])) {
            return;
        }

        Schema::table('schedules', function (Blueprint $table): void {
            $table->timestamp('reminder_sent_day_before_at')->nullable()->after('cancelled_at');
            $table->timestamp('reminder_sent_hours_before_at')->nullable()->after('reminder_sent_day_before_at');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumns('schedules', ['reminder_sent_day_before_at', 'reminder_sent_hours_before_at'])) {
            Schema::table('schedules', function (Blueprint $table): void {
                $table->dropColumn(['reminder_sent_day_before_at', 'reminder_sent_hours_before_at']);
            });
        }
    }
};
