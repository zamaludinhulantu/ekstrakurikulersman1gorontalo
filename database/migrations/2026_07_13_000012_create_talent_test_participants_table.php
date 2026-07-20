<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_test_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('attendance_status', ['pending', 'present', 'absent', 'sick', 'permission'])->default('pending');
            $table->text('attendance_notes')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'student_id']);
            $table->index(['registration_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_test_participants');
    }
};
