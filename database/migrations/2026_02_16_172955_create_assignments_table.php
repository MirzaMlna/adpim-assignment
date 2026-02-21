<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->string('agency');
            $table->date('date');
            $table->time('time');
            $table->integer('day_count')->default(1);
            $table->string('location');
            $table->string('location_detail')->nullable();
            $table->decimal('fee_per_day', 15, 2)->default(0);
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
