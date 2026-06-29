<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_coach', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extracurricular_id')->constrained()->cascadeOnDelete();
            $table->foreignId('coach_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['extracurricular_id', 'coach_id']);
        });

        $rows = DB::table('extracurriculars')
            ->select('id as extracurricular_id', 'coach_id')
            ->whereNotNull('coach_id')
            ->get()
            ->map(fn ($row) => [
                'extracurricular_id' => $row->extracurricular_id,
                'coach_id' => $row->coach_id,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($rows !== []) {
            DB::table('extracurricular_coach')->insert($rows);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_coach');
    }
};
