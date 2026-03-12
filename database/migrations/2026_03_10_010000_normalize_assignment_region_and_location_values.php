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

        DB::table('assignments')
            ->whereIn('region_classification', ['dalam_daerah_kabupaten', 'Dalam Daerah Kabupaten'])
            ->update(['region_classification' => 'luar_daerah_kabupaten']);

        DB::table('assignments')
            ->where('region_classification', 'Dalam Daerah')
            ->update(['region_classification' => 'dalam_daerah']);

        DB::table('assignments')
            ->where('region_classification', 'Luar Daerah')
            ->update(['region_classification' => 'luar_daerah']);

        $locationMappings = [
            'Banjarmasin' => 'Kota Banjarmasin',
            'Banjarbaru' => 'Kota Banjarbaru',
            'Banjar' => 'Kab. Banjar',
            'Barito Kuala' => 'Kab. Barito Kuala',
            'Hulu Sungai Selatan' => 'Kab. Hulu Sungai Selatan',
            'Hulu Sungai Tengah' => 'Kab. Hulu Sungai Tengah',
            'Hulu Sungai Utara' => 'Kab. Hulu Sungai Utara',
            'Balangan' => 'Kab. Balangan',
            'Kotabaru' => 'Kab. Kotabaru',
            'Tabalong' => 'Kab. Tabalong',
            'Tanah Laut' => 'Kab. Tanah Laut',
            'Tanah Bumbu' => 'Kab. Tanah Bumbu',
            'Tapin' => 'Kab. Tapin',
        ];

        foreach ($locationMappings as $oldValue => $newValue) {
            DB::table('assignments')
                ->where('location', $oldValue)
                ->update(['location' => $newValue]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('assignments')) {
            return;
        }

        DB::table('assignments')
            ->where('region_classification', 'luar_daerah_kabupaten')
            ->update(['region_classification' => 'dalam_daerah_kabupaten']);

        $locationMappings = [
            'Kota Banjarmasin' => 'Banjarmasin',
            'Kota Banjarbaru' => 'Banjarbaru',
            'Kab. Banjar' => 'Banjar',
            'Kab. Barito Kuala' => 'Barito Kuala',
            'Kab. Hulu Sungai Selatan' => 'Hulu Sungai Selatan',
            'Kab. Hulu Sungai Tengah' => 'Hulu Sungai Tengah',
            'Kab. Hulu Sungai Utara' => 'Hulu Sungai Utara',
            'Kab. Balangan' => 'Balangan',
            'Kab. Kotabaru' => 'Kotabaru',
            'Kab. Tabalong' => 'Tabalong',
            'Kab. Tanah Laut' => 'Tanah Laut',
            'Kab. Tanah Bumbu' => 'Tanah Bumbu',
            'Kab. Tapin' => 'Tapin',
        ];

        foreach ($locationMappings as $newValue => $oldValue) {
            DB::table('assignments')
                ->where('location', $newValue)
                ->update(['location' => $oldValue]);
        }
    }
};
