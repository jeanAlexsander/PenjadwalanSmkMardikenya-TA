<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\Guru;

class KelasController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        // 1) Tabel utama (PAGINATED)
        $kelases = Kelas::with(['jurusan', 'waliKelas.user'])
            ->orderBy('nama_kelas', 'asc')
            ->paginate($perPage)
            ->withQueryString();

        // 2) Data pendukung (dropdown)
        $jurusans = Jurusan::orderBy('nama_jurusan', 'asc')->get();

        // 3) Semua guru (+user) untuk opsi wali
        $allGurus = Guru::with('user')->get()->keyBy('id');

        // 4) Ambil SEMUA wali_kelas_id yang sedang terpakai (bukan hanya halaman aktif)
        $waliDipakai = Kelas::whereNotNull('wali_kelas_id')->pluck('wali_kelas_id'); // Collection of IDs

        // 5) Form TAMBAH: hanya guru yang belum dipakai di kelas manapun
        $gurusAvailableForCreate = $allGurus->except($waliDipakai->all())->values();

        // 6) Form EDIT: untuk setiap kelas DI HALAMAN INI,
        //    tampilkan semua guru yang belum jadi wali DI KELAS LAIN,
        //    + tetap sertakan wali saat ini agar tidak “hilang”
        $gurusOptionsPerKelas = [];
        foreach ($kelases as $k) {
            $waliKecualiKelasIni = $waliDipakai->reject(fn($gid) => (int)$gid === (int)$k->wali_kelas_id);
            $gurusOptionsPerKelas[$k->id] = $allGurus
                ->except($waliKecualiKelasIni->all())
                ->values();
        }

        return view('admin.kelas.index', compact(
            'kelases',
            'jurusans',
            'gurusAvailableForCreate',
            'gurusOptionsPerKelas'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas'    => 'required|string',
            'tingkat'       => 'required|integer|in:10,11,12',
            'jurusan_id'    => 'required|exists:jurusans,id',
            // Pastikan 1 guru hanya bisa jadi wali 1 kelas
            'wali_kelas_id' => [
                'nullable',
                'exists:gurus,id',
                Rule::unique('kelas', 'wali_kelas_id'), // unik di tabel kelas
            ],
        ]);

        Kelas::create([
            'nama_kelas'    => $request->nama_kelas,
            'tingkat'       => $request->tingkat,
            'jurusan_id'    => $request->jurusan_id,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        return redirect()->back()->with('toast_success', 'Data kelas berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $request->validate([
            'nama_kelas'    => 'required|string|max:255',
            'jurusan_id'    => 'required|exists:jurusans,id',
            'tingkat'       => 'required|in:10,11,12',
            'wali_kelas_id' => [
                'nullable',
                'exists:gurus,id',
                // unik, tapi abaikan baris kelas yang sedang diedit
                Rule::unique('kelas', 'wali_kelas_id')->ignore($kelas->id),
            ],
        ]);

        $kelas->update([
            'nama_kelas'    => $request->nama_kelas,
            'jurusan_id'    => $request->jurusan_id,
            'tingkat'       => $request->tingkat,
            'wali_kelas_id' => $request->wali_kelas_id,
        ]);

        return redirect()->route('admin.kelas.index')->with('toast_success', 'Kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        $kelas->delete();

        return redirect()->route('admin.kelas.index')->with('toast_success', 'Kelas berhasil dihapus.');
    }
}
