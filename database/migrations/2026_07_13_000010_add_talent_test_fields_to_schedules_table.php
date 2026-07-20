<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table): void {
            $table->enum('schedule_type', ['activity', 'talent_test'])->default('activity')->after('coach_id');
            $table->enum('status', ['scheduled', 'cancelled', 'completed'])->default('scheduled')->after('description');
            $table->text('equipment')->nullable()->after('status');
            $table->text('instructions')->nullable()->after('equipment');
            $table->timestamp('cancelled_at')->nullable()->after('instructions');

            $table->index(['schedule_type', 'activity_date']);
            $table->index(['status', 'activity_date']);
        });
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table): void {
            $table->dropIndex(['schedule_type', 'activity_date']);
            $table->dropIndex(['status', 'activity_date']);
            $table->dropColumn([
                'schedule_type',
                'status',
                'equipment',
                'instructions',
                'cancelled_at',
            ]);
        });
    }
};
