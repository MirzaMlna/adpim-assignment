<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->date('boarding_date')->nullable()->after('date');
            $table->date('return_date')->nullable()->after('boarding_date');
            $table->string('transportation')->nullable()->after('return_date');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropColumn(['boarding_date', 'return_date', 'transportation']);
        });
    }
};
