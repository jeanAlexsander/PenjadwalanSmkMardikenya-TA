<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas'; // opsional jika nama tabel sudah benar

    protected $fillable = [
        'nama_kelas',
        'jurusan_id',
        'tingkat', // jika kamu menggunakan jurusan
        'wali_kelas_id', // jika ada relasi ke guru

    ];

    public function jadwalKhusus()
    {
        return $this->belongsToMany(JadwalKhusus::class, 'jadwal_khusus_kelas');
    }

    // Opsional: jika kamu punya relasi ke jurusan
    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class);
    }

    // Opsional: jika kamu punya relasi ke guru sebagai wali kelas
    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'wali_kelas_id');
    }

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }

    public function kebutuhanMapelKelas()
    {
        return $this->hasMany(KebutuhanMapelKelas::class);
    }
}
