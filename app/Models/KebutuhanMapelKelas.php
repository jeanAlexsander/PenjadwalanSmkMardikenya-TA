<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KebutuhanMapelKelas extends Model
{
    // Nama tabel
    protected $table = 'kebutuhan_mapel_kelas';

    // Kolom yang boleh diisi mass assignment
    protected $fillable = [
        'kelas_id',
        'guru_mata_pelajaran_id',
        'jumlah_jam_per_minggu',
    ];

    protected $casts = [
        'kelas_id'               => 'integer',
        'guru_mata_pelajaran_id' => 'integer',
        'jumlah_jam'             => 'integer',
    ];

    /**
     * Relasi ke model Kelas
     */
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Relasi ke model GuruMapel
     */
    public function guruMapel()
    {
        return $this->belongsTo(GuruMapel::class, 'guru_mata_pelajaran_id');
    }
}
