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
            $table->json('branch_options')->nullable()->after('schedule_overview');
        });

        Schema::table('registrations', function (Blueprint $table) {
            $table->string('selected_branch')->nullable()->after('extracurricular_id');
        });

        DB::table('extracurriculars')->where('name', 'OSN')->update([
            'branch_options' => json_encode([
                'Matematika',
                'IPA Terpadu',
                'Geografi',
                'Ekonomi',
                'Kebumian',
                'Astronomi',
                'Informatika',
            ]),
        ]);

        DB::table('extracurriculars')->where('name', 'O2SN')->update([
            'branch_options' => json_encode([
                'Silat',
                'Karate & Taekwondo',
                'Renang',
                'Badminton',
                'Atletik',
                'Panjat Tebing',
                'Tenis Meja',
                'Takraw',
                'Volly Ball',
                'Basket Ball',
                'Futsal / Sepak Bola',
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn('selected_branch');
        });

        Schema::table('extracurriculars', function (Blueprint $table) {
            $table->dropColumn('branch_options');
        });
    }
};
