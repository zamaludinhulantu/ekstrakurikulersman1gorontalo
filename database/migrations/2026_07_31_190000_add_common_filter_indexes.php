<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->index(['status', 'registration_date'], 'registrations_status_date_index');
        });

        Schema::table('assessments', function (Blueprint $table): void {
            $table->index(['status', 'assessment_date'], 'assessments_status_date_index');
        });

        Schema::table('announcements', function (Blueprint $table): void {
            $table->index(
                ['publication_status', 'is_active', 'publish_at'],
                'announcements_publication_active_publish_index'
            );
        });
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->dropIndex('announcements_publication_active_publish_index');
        });

        Schema::table('assessments', function (Blueprint $table): void {
            $table->dropIndex('assessments_status_date_index');
        });

        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropIndex('registrations_status_date_index');
        });
    }
};
