<?php


namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jurusan;

class JurusanMonitorController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 14);

        $dataJurusan = Jurusan::orderBy('nama_jurusan', 'asc')
            ->paginate($perPage)
            ->withQueryString(); // bawa query saat pindah halaman

        return view('kepala_sekolah.jurusan.index', compact('dataJurusan'));
    }
}
