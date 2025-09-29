<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jadwal_pelajaran_history', function (Blueprint $table) {
            $table->id();

            // satu aksi (mis. klik reset) = satu batch
            $table->uuid('batch_key')->index();

            // referensi ke baris asal (boleh null agar histori tetap ada meski asal dihapus)
            $table->foreignId('jadwal_pelajaran_id')->nullable()
                ->constrained('jadwal_pelajaran')->nullOnDelete();

            // mirror kolom penting dari jadwal_pelajaran
            $table->tinyInteger('hari');       // 1..6
            $table->tinyInteger('jam');        // 0..12
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->foreignId('guru_mata_pelajaran_id')->nullable()
                ->constrained('guru_mata_pelajaran')->nullOnDelete();
            $table->foreignId('ruangan_id')->nullable()
                ->constrained('ruangan')->nullOnDelete();
            $table->enum('jenis', ['MAPEL', 'ISTIRAHAT', 'EKSKUL', 'UPACARA', 'KEGIATAN'])->default('MAPEL');

            // meta histori
            $table->enum('aksi', ['RESET', 'GENERATE', 'DELETE', 'UPDATE', 'CREATE'])->default('RESET')->index();
            $table->timestamp('waktu_aksi')->useCurrent();

            // bantu tampilan cepat + audit
            $table->string('snapshot_text', 255)->nullable();

            $table->timestamps();

            $table->index(['kelas_id', 'hari', 'jam']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelajaran_history');
    }
};
