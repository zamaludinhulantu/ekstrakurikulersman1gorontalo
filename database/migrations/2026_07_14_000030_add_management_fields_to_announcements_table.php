<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->string('priority', 20)->default('normal')->after('content');
            $table->string('publication_status', 20)->default('published')->after('published_by');
            $table->timestamp('publish_at')->nullable()->after('publication_status');
            $table->timestamp('ends_at')->nullable()->after('publish_at');
            $table->string('attachment_path')->nullable()->after('ends_at');
            $table->string('attachment_name')->nullable()->after('attachment_path');
        });

        DB::table('announcements')->update([
            'priority' => 'normal',
            'publication_status' => DB::raw("CASE WHEN is_active = 1 THEN 'published' ELSE 'inactive' END"),
            'publish_at' => DB::raw('created_at'),
        ]);
    }

    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn(['priority', 'publication_status', 'publish_at', 'ends_at', 'attachment_path', 'attachment_name']);
        });
    }
};
