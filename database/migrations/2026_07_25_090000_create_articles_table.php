<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('excerpt', 320)->nullable();
            $table->longText('content');
            $table->foreignId('extracurricular_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('published_by')->constrained('users')->cascadeOnDelete();
            $table->string('image_path')->nullable();
            $table->string('image_name')->nullable();
            $table->enum('publication_status', ['draft', 'scheduled', 'published', 'archived', 'inactive'])->default('draft');
            $table->timestamp('publish_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['publication_status', 'is_active']);
            $table->index(['extracurricular_id', 'publish_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
