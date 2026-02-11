<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_division_id')
                ->constrained('sub_divisions')
                ->cascadeOnDelete();

            $table->string('code')->unique(); // ADP-xxx
            $table->string('email')->unique();
            $table->string('password'); // WAJIB untuk autentikasi

            $table->string('nip');
            $table->string('name');
            $table->string('rank');
            $table->string('job_title');

            $table->enum('role', ['ADMIN', 'STAFF', 'PIMPINAN ADPIM']);
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();

            $table->rememberToken(); // tetap disarankan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
