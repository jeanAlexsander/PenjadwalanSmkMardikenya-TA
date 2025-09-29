<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuruMapel extends Model
{
    protected $table = 'guru_mata_pelajaran'; // pastikan nama tabelnya sesuai

    protected $fillable = [
        'guru_id',
        'mata_pelajaran_id',
        'jenis',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mata_pelajaran_id');
    }

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class, 'guru_mata_pelajaran_id');
    }

    public function kebutuhanMapelKelas()
    {
        return $this->hasMany(KebutuhanMapelKelas::class);
    }
}
