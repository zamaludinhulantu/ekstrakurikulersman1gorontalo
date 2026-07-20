<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        foreach ([
            ['index' => 'registrations_student_status_index', 'table' => 'registrations', 'statement' => 'CREATE INDEX registrations_student_status_index ON registrations (student_id, status)'],
            ['index' => 'registrations_extracurricular_status_index', 'table' => 'registrations', 'statement' => 'CREATE INDEX registrations_extracurricular_status_index ON registrations (extracurricular_id, status)'],
            ['index' => 'schedules_extracurricular_activity_date_index', 'table' => 'schedules', 'statement' => 'CREATE INDEX schedules_extracurricular_activity_date_index ON schedules (extracurricular_id, activity_date)'],
            ['index' => 'schedules_coach_activity_date_index', 'table' => 'schedules', 'statement' => 'CREATE INDEX schedules_coach_activity_date_index ON schedules (coach_id, activity_date)'],
            ['index' => 'attendances_student_recorded_at_index', 'table' => 'attendances', 'statement' => 'CREATE INDEX attendances_student_recorded_at_index ON attendances (student_id, recorded_at)'],
            ['index' => 'attendances_schedule_status_index', 'table' => 'attendances', 'statement' => 'CREATE INDEX attendances_schedule_status_index ON attendances (schedule_id, status)'],
            ['index' => 'attendances_extracurricular_status_index', 'table' => 'attendances', 'statement' => 'CREATE INDEX attendances_extracurricular_status_index ON attendances (extracurricular_id, status)'],
            ['index' => 'assessments_student_assessment_date_index', 'table' => 'assessments', 'statement' => 'CREATE INDEX assessments_student_assessment_date_index ON assessments (student_id, assessment_date)'],
            ['index' => 'assessments_extracurricular_assessment_date_index', 'table' => 'assessments', 'statement' => 'CREATE INDEX assessments_extracurricular_assessment_date_index ON assessments (extracurricular_id, assessment_date)'],
            ['index' => 'announcements_published_by_is_active_index', 'table' => 'announcements', 'statement' => 'CREATE INDEX announcements_published_by_is_active_index ON announcements (published_by, is_active)'],
        ] as $definition) {
            if ($driver === 'mysql') {
                $exists = DB::table('information_schema.statistics')
                    ->where('table_schema', DB::getDatabaseName())
                    ->where('table_name', $definition['table'])
                    ->where('index_name', $definition['index'])
                    ->exists();

                if (! $exists) {
                    DB::statement($definition['statement']);
                }
            } else {
                DB::statement(str_replace('CREATE INDEX', 'CREATE INDEX IF NOT EXISTS', $definition['statement']));
            }
        }
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
