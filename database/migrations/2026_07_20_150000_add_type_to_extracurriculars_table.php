<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extracurriculars', function (Blueprint $table) {
            $table->string('type', 32)->default('extracurricular')->after('coach_id');
        });

        DB::table('extracurriculars')
            ->whereNull('type')
            ->update(['type' => 'extracurricular']);
    }

    public function down(): void
    {
        Schema::table('extracurriculars', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
