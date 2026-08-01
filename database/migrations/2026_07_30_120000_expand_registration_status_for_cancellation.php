<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE `registrations`
                MODIFY `status` ENUM('pending', 'approved', 'rejected', 'cancelled')
                NOT NULL DEFAULT 'pending'
            ");

            return;
        }

        Schema::table('registrations', function (Blueprint $table) {
            $table->string('status', 32)->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        DB::table('registrations')
            ->where('status', 'cancelled')
            ->update(['status' => 'rejected']);

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE `registrations`
                MODIFY `status` ENUM('pending', 'approved', 'rejected')
                NOT NULL DEFAULT 'pending'
            ");

            return;
        }

        Schema::table('registrations', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};
