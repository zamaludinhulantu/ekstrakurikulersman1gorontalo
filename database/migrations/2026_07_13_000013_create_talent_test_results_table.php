<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_test_results', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coach_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->decimal('overall_score', 6, 2)->nullable();
            $table->string('ability_category', 120)->nullable();
            $table->string('training_group', 120)->nullable();
            $table->string('recommended_role', 120)->nullable();
            $table->text('recommendation')->nullable();
            $table->text('coach_notes')->nullable();
            $table->text('internal_notes')->nullable();
            $table->boolean('needs_retest')->default(false);
            $table->foreignId('retest_schedule_id')->nullable()->constrained('schedules')->nullOnDelete();
            $table->timestamp('evaluated_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'student_id']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_test_results');
    }
};
