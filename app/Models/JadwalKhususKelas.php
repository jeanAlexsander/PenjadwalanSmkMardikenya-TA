<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalKhususKelas extends Model
{
    protected $table = 'jadwal_khusus_kelas';
    public $timestamps = false; // karena pivot biasanya tidak punya created_at & updated_at

    protected $fillable = [
        'jadwal_khusus_id',
        'kelas_id',
    ];

    public function jadwalKhusus()
    {
        return $this->belongsTo(JadwalKhusus::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }
}
