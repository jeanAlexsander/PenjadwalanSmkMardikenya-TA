<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('jadwal_khusus_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jadwal_khusus_id')->constrained('jadwal_khusus')->onDelete('cascade');
            $table->foreignId('kelas_id')->constrained('kelas')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('jadwal_khusus_kelas');
    }
};
