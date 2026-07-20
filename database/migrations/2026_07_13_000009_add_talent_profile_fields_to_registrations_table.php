<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->text('motivation_reason')->nullable()->after('notes');
            $table->text('goal_statement')->nullable()->after('motivation_reason');
            $table->text('prior_experience')->nullable()->after('goal_statement');
            $table->text('current_skills')->nullable()->after('prior_experience');
            $table->string('primary_talent', 255)->nullable()->after('current_skills');
            $table->string('preferred_position', 255)->nullable()->after('primary_talent');
            $table->enum('skill_level', ['pemula', 'menengah', 'mahir'])->nullable()->after('preferred_position');
            $table->text('achievement_history')->nullable()->after('skill_level');
            $table->string('achievement_proof_path')->nullable()->after('achievement_history');
            $table->boolean('willing_to_take_test')->default(false)->after('achievement_proof_path');
            $table->text('student_notes')->nullable()->after('willing_to_take_test');
            $table->boolean('allow_public_profile')->default(false)->after('student_notes');
        });
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropColumn([
                'motivation_reason',
                'goal_statement',
                'prior_experience',
                'current_skills',
                'primary_talent',
                'preferred_position',
                'skill_level',
                'achievement_history',
                'achievement_proof_path',
                'willing_to_take_test',
                'student_notes',
                'allow_public_profile',
            ]);
        });
    }
};
