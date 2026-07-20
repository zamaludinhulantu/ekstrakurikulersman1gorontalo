<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_test_result_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('talent_test_result_id')->constrained()->cascadeOnDelete();
            $table->foreignId('talent_test_aspect_id')->constrained()->cascadeOnDelete();
            $table->decimal('score', 6, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['talent_test_result_id', 'talent_test_aspect_id'], 'talent_test_result_items_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_test_result_items');
    }
};
