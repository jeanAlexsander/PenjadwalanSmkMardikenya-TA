<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKhusus extends Model
{
    protected $table = 'jadwal_khusus';

    protected $fillable = [
        'nama_kegiatan',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'ruangan_id',
        'keterangan',
        'untuk_semua_kelas',
    ];

    public function kelas()
    {
        return $this->belongsToMany(Kelas::class, 'jadwal_khusus_kelas');
    }


    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class);
    }
}
