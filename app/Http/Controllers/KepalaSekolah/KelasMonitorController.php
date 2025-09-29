<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Guru;


class KelasMonitorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 1);

        // Tabel utama (kepala sekolah) — dipaginasi
        $kelases = Kelas::with(['jurusan', 'waliKelas.user'])
            ->orderBy('nama_kelas', 'asc')
            ->paginate($perPage)
            ->withQueryString(); // bawa ?per_page (atau filter lain) saat pindah halaman

        // Data pendukung (tanpa paginate)
        $gurus    = Guru::with('user')->get();
        $jurusans = Jurusan::orderBy('nama_jurusan', 'asc')->get();

        return view('kepala_sekolah.kelas.index', compact('kelases', 'gurus', 'jurusans'));
    }
}
