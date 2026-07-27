<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('talent_test_results', function (Blueprint $table): void {
            $table->enum('decision_status', ['accepted', 'rejected'])->nullable()->after('ability_category');
            $table->text('decision_notes')->nullable()->after('decision_status');
            $table->timestamp('decided_at')->nullable()->after('decision_notes');
            $table->index('decision_status');
        });
    }

    public function down(): void
    {
        Schema::table('talent_test_results', function (Blueprint $table): void {
            $table->dropIndex(['decision_status']);
            $table->dropColumn(['decision_status', 'decision_notes', 'decided_at']);
        });
    }
};
