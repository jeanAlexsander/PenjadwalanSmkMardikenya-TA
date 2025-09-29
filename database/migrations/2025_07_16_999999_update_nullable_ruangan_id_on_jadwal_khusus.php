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
        Schema::table('jadwal_khusus', function (Blueprint $table) {
            $table->dropForeign(['ruangan_id']);
            $table->foreignId('ruangan_id')->nullable()->change();
            $table->foreign('ruangan_id')->references('id')->on('ruangan')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
