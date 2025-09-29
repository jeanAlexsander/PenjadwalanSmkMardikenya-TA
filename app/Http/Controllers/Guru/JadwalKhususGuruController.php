<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalKhusus;
use App\Models\Kelas;
use App\Models\Ruangan;
use Carbon\Carbon;

class JadwalKhususGuruController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        // "Hari ini" sesuai zona Asia/Jakarta
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        $jadwal = JadwalKhusus::with(['kelas', 'ruangan'])
            ->whereDate('tanggal', '>=', $today->toDateString())
            ->orderBy('tanggal', 'asc')
            // ->orderBy('jam_mulai', 'asc') // opsional kalau ada kolom jam
            ->paginate($perPage)
            ->withQueryString();

        // Dropdown (tanpa paginate)
        $kelasList   = Kelas::orderBy('nama_kelas', 'asc')->get();
        $ruanganList = Ruangan::orderBy('nama', 'asc')->get();

        return view('guru.jadwal_khusus.index', compact('jadwal', 'kelasList', 'ruanganList'));
    }
}
