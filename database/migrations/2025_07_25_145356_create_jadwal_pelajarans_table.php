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
        Schema::create('jadwal_pelajaran', function (Blueprint $table) {
            $table->id();

            // 1=Senin … 6=Sabtu (boleh pakai 0..12 untuk jam nol; DB tidak membatasi rentang)
            $table->tinyInteger('hari');   // 1..6
            $table->tinyInteger('jam');    // jam-ke (0/1 .. 12)

            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('guru_mata_pelajaran_id')->nullable()->constrained('guru_mata_pelajaran')->nullOnDelete(); // boleh null untuk ISTIRAHAT/EKSKUL
            $table->foreignId('ruangan_id')->nullable()->constrained('ruangan')->nullOnDelete();
            // Slot jenis khusus (biar GA/UI gampang)
            $table->enum('jenis', ['MAPEL', 'ISTIRAHAT', 'EKSKUL', 'UPACARA', 'KEGIATAN'])->default('MAPEL');
            $table->timestamps();
            // Anti-bentrok: satu kelas & satu guru tak boleh dobel slot di waktu yang sama
            $table->unique(['kelas_id', 'hari', 'jam'], 'uniq_kelas_slot');
            $table->unique(['guru_mata_pelajaran_id', 'hari', 'jam'], 'uniq_guru_slot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajaran');
    }
};
