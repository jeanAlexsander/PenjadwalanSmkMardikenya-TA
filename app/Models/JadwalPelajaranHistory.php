<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPelajaranHistory extends Model
{
    protected $table = 'jadwal_pelajaran_history';

    protected $fillable = [
        'batch_key',
        'jadwal_pelajaran_id',
        'hari',
        'jam',
        'kelas_id',
        'guru_mata_pelajaran_id',
        'ruangan_id',
        'jenis',
        'aksi',          // RESET | GENERATE | DELETE | UPDATE | CREATE
        'waktu_aksi',
        'snapshot_text'
    ];

    protected $casts = [
        'hari'        => 'integer',
        'jam'         => 'integer',
        'waktu_aksi'  => 'datetime',
    ];

    public function jadwal()
    {
        return $this->belongsTo(JadwalPelajaran::class, 'jadwal_pelajaran_id');
    }

    public function actedBy()
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
