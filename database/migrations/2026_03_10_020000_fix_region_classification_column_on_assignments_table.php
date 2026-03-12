<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assignments')) {
            return;
        }

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `assignments` MODIFY `region_classification` VARCHAR(64) NOT NULL DEFAULT 'dalam_daerah'");
        }

        DB::table('assignments')
            ->whereIn('region_classification', ['Dalam Daerah', 'DALAM_DAERAH'])
            ->update(['region_classification' => 'dalam_daerah']);

        DB::table('assignments')
            ->whereIn('region_classification', ['Dalam Daerah Kabupaten', 'dalam_daerah_kabupaten', 'DALAM_DAERAH_KABUPATEN'])
            ->update(['region_classification' => 'luar_daerah_kabupaten']);

        DB::table('assignments')
            ->whereIn('region_classification', ['Luar Daerah', 'LUAR_DAERAH'])
            ->update(['region_classification' => 'luar_daerah']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('assignments')) {
            return;
        }

        DB::table('assignments')
            ->whereNotIn('region_classification', ['dalam_daerah', 'luar_daerah_kabupaten', 'luar_daerah'])
            ->update(['region_classification' => 'luar_daerah']);

        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE `assignments` MODIFY `region_classification` ENUM('dalam_daerah','luar_daerah_kabupaten','luar_daerah') NOT NULL DEFAULT 'dalam_daerah'");
        }
    }
};
