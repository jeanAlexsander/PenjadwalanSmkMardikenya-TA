<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MataPelajaran extends Model
{
    use HasFactory;

    protected $table = 'mata_pelajaran';

    protected $fillable = [
        'nama_mata_pelajaran',
        'kode_mata_pelajaran',
    ];

    public function guruMapel()
    {
        return $this->hasMany(\App\Models\GuruMapel::class);
    }

    public function guru()
    {
        return $this->belongsToMany(Guru::class, 'guru_mata_pelajaran');
    }
}
