<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('slug')->unique();
            $table->string('label');
            $table->text('description')->nullable();
            $table->string('catalog_title');
            $table->text('catalog_subtitle')->nullable();
            $table->string('icon', 80)->default('bi-grid-1x2');
            $table->string('tone', 80)->default('is-extracurricular');
            $table->string('image_path')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_categories');
    }
};
