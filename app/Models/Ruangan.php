<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    use HasFactory;

    protected $table = 'ruangan'; // optional, hanya jika nama tabel tidak jamak

    protected $fillable = [
        'nama',
        'kapasitas',
        'keterangan',
    ];

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class);
    }
}
