<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalPelajaran extends Model
{

    protected $table = 'jadwal_pelajaran'; // 👈 tambahkan baris ini!
    protected $fillable = [
        'hari',
        'jam',
        'kelas_id',
        'guru_mata_pelajaran_id',
        'ruangan_id',
        'jenis',
    ];

    protected $casts = [
        'hari' => 'integer',
        'jam'  => 'integer',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guruMapel()
    {
        return $this->belongsTo(GuruMapel::class, 'guru_mata_pelajaran_id');
    }

    public function ruangan()
    {
        return $this->belongsTo(Ruangan::class, 'ruangan_id');
    }

    // 🔹 Relasi ke histori (satu jadwal bisa punya banyak histori perubahan)
    public function histories()
    {
        return $this->hasMany(JadwalPelajaranHistory::class, 'jadwal_pelajaran_id');
    }

    // 🔹 Accessor opsional → konversi jam-ke ke jam real
    public function getWaktuAttribute(): array
    {
        return hitungJam($this->jam);
        // fungsi helper yg mengembalikan ['mulai' => '07:00', 'selesai' => '07:45']
    }
}
