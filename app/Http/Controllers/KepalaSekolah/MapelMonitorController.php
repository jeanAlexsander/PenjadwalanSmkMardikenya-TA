<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapelMonitorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 14);

        $mataPelajaran = MataPelajaran::orderBy('nama_mata_pelajaran', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        return view('kepala_sekolah.mapel.index', compact('mataPelajaran'));
    }
}
