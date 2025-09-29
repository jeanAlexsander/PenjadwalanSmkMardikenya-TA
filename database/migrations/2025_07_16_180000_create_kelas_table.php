<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kelas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kelas'); // contoh: 10 Kuliner A
            $table->unsignedBigInteger('jurusan_id')->nullable(); // foreign key ke jurusan
            $table->tinyInteger('tingkat'); // 10, 11, 12
            $table->unsignedBigInteger('wali_kelas_id')->nullable(); // foreign key ke guru (wali kelas)
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('jurusan_id')->references('id')->on('jurusans')->nullOnDelete();
            $table->foreign('wali_kelas_id')->references('id')->on('gurus')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kelas');
    }
};
