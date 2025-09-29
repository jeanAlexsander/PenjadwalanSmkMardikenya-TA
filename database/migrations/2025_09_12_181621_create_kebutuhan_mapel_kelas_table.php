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
        Schema::create('kebutuhan_mapel_kelas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            // konsisten dengan nama tabel rujukan
            $table->foreignId('guru_mata_pelajaran_id')->constrained('guru_mata_pelajaran')->cascadeOnDelete();
            $table->unsignedTinyInteger('jumlah_jam_per_minggu');
            $table->timestamps();
            $table->unique(['kelas_id', 'guru_mata_pelajaran_id'], 'uniq_kelas_gmp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kebutuhan_mapel_kelas');
    }
};
