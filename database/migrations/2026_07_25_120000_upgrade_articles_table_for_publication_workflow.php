<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->string('content_category', 50)->default('activity_news')->after('content');
            $table->string('image_alt_text', 255)->nullable()->after('image_name');
            $table->string('meta_description', 180)->nullable()->after('image_alt_text');
            $table->timestamp('expires_at')->nullable()->after('publish_at');
            $table->boolean('is_featured')->default(false)->after('expires_at');
        });

        DB::table('articles')
            ->whereNull('content_category')
            ->update([
                'content_category' => 'activity_news',
                'image_alt_text' => DB::raw('coalesce(image_alt_text, title)'),
                'meta_description' => DB::raw('coalesce(meta_description, excerpt)'),
            ]);
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table): void {
            $table->dropColumn([
                'content_category',
                'image_alt_text',
                'meta_description',
                'expires_at',
                'is_featured',
            ]);
        });
    }
};
