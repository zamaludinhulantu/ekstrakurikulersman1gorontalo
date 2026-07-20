<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('is_late')->default(false)->after('status');
            $table->string('save_state', 20)->default('finalized')->after('is_late');
            $table->timestamp('finalized_at')->nullable()->after('recorded_at');
        });

        DB::table('attendances')
            ->update([
                'is_late' => false,
                'save_state' => 'finalized',
                'finalized_at' => DB::raw('COALESCE(recorded_at, updated_at, created_at)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['is_late', 'save_state', 'finalized_at']);
        });
    }
};
