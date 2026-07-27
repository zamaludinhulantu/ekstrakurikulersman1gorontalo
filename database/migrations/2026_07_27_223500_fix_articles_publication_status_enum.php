<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE articles
            MODIFY publication_status
            ENUM('draft', 'scheduled', 'published', 'archived', 'inactive')
            NOT NULL DEFAULT 'draft'
        ");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE articles
            MODIFY publication_status
            ENUM('draft', 'scheduled', 'published', 'inactive')
            NOT NULL DEFAULT 'draft'
        ");
    }
};
