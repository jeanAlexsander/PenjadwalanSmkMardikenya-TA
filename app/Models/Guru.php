<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;
use App\Models\User;

class Guru extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'nip',
        'name',
        'jenis_kelamin',
        'alamat',
        'email',
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kelas()
    {
        return $this->hasOne(Kelas::class, 'wali_kelas_id');
    }

    public function guruMapel()
    {
        return $this->hasMany(GuruMapel::class, 'guru_id');
    }

    public function kelasDiampu(): Collection
    {
        return $this->guruMapel
            ->flatMap(function ($gm) {
                return $gm->jadwalPelajaran->pluck('kelas');
            })
            ->filter() // buang null
            ->unique('id') // hilangkan duplikat
            ->values(); // reset index
    }
}
