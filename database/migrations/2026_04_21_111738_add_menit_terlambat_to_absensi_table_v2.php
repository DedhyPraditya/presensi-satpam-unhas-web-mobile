<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('absensi', 'menit_terlambat')) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->integer('menit_terlambat')->default(0)->after('terlambat');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('absensi', 'menit_terlambat')) {
            Schema::table('absensi', function (Blueprint $table) {
                $table->dropColumn('menit_terlambat');
            });
        }
    }
};
