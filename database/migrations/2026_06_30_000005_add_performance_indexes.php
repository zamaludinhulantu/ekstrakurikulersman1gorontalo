<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('CREATE INDEX IF NOT EXISTS registrations_student_status_index ON registrations (student_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS registrations_extracurricular_status_index ON registrations (extracurricular_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS schedules_extracurricular_activity_date_index ON schedules (extracurricular_id, activity_date)');
        DB::statement('CREATE INDEX IF NOT EXISTS schedules_coach_activity_date_index ON schedules (coach_id, activity_date)');
        DB::statement('CREATE INDEX IF NOT EXISTS attendances_student_recorded_at_index ON attendances (student_id, recorded_at)');
        DB::statement('CREATE INDEX IF NOT EXISTS attendances_schedule_status_index ON attendances (schedule_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS attendances_extracurricular_status_index ON attendances (extracurricular_id, status)');
        DB::statement('CREATE INDEX IF NOT EXISTS assessments_student_assessment_date_index ON assessments (student_id, assessment_date)');
        DB::statement('CREATE INDEX IF NOT EXISTS assessments_extracurricular_assessment_date_index ON assessments (extracurricular_id, assessment_date)');
        DB::statement('CREATE INDEX IF NOT EXISTS announcements_published_by_is_active_index ON announcements (published_by, is_active)');
    }

    public function down(): void
    {
        $driver = DB::getDriverName();

        foreach ([
            ['registrations_student_status_index', 'registrations'],
            ['registrations_extracurricular_status_index', 'registrations'],
            ['schedules_extracurricular_activity_date_index', 'schedules'],
            ['schedules_coach_activity_date_index', 'schedules'],
            ['attendances_student_recorded_at_index', 'attendances'],
            ['attendances_schedule_status_index', 'attendances'],
            ['attendances_extracurricular_status_index', 'attendances'],
            ['assessments_student_assessment_date_index', 'assessments'],
            ['assessments_extracurricular_assessment_date_index', 'assessments'],
            ['announcements_published_by_is_active_index', 'announcements'],
        ] as [$index, $table]) {
            if ($driver === 'mysql') {
                DB::statement("DROP INDEX {$index} ON {$table}");
            } else {
                DB::statement("DROP INDEX IF EXISTS {$index}");
            }
        }
    }
};
