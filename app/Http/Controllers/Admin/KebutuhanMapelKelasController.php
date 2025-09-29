<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\KebutuhanMapelKelas;
use App\Models\Kelas;
use App\Models\GuruMapel; // model yg map ke tabel guru_mata_pelajaran

class KebutuhanMapelKelasController extends Controller
{
    public function index(Request $request)
    {
        $perPage   = (int) $request->query('per_page', 10);
        $kelasNama = $request->query('kelas');
        $kelasTerpilih = null;

        if ($kelasNama) {
            $kelasTerpilih = Kelas::where('nama_kelas', $kelasNama)->first();
        }

        // dropdown kelas
        $listKelas = Kelas::orderBy('nama_kelas')->get();

        // list guru-mapel untuk form (biarkan full, tidak dipaginasi)
        $guruMapel = GuruMapel::query()
            ->select('guru_mata_pelajaran.*') // penting agar model utuh
            ->leftJoin('mata_pelajaran as m', 'm.id', '=', 'guru_mata_pelajaran.mata_pelajaran_id')
            ->with(['guru.user', 'mataPelajaran'])
            ->orderBy('m.nama_mata_pelajaran', 'asc')
            ->orderBy('guru_mata_pelajaran.guru_id', 'asc')
            ->get();

        // TABEL UTAMA: PAGINATION
        $kebutuhan = KebutuhanMapelKelas::with([
            'kelas',
            'guruMapel.guru.user',
            'guruMapel.mataPelajaran',
        ])
            ->when($kelasTerpilih, fn($q) => $q->where('kelas_id', $kelasTerpilih->id))
            ->orderBy('kelas_id')
            ->orderBy('guru_mata_pelajaran_id')
            ->paginate($perPage)         // <<--- paginate
            ->withQueryString();         // <<--- bawa ?kelas & ?per_page

        return view(
            'admin.kebutuhan_kelas_mapel.index',
            compact('listKelas', 'kelasTerpilih', 'guruMapel', 'kebutuhan')
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id'               => ['required', 'exists:kelas,id'],
            'guru_mata_pelajaran_id' => [
                'required',
                'exists:guru_mata_pelajaran,id',
                // unik per (kelas, guru_mapel)
                Rule::unique('kebutuhan_mapel_kelas')
                    ->where(fn($q) => $q->where('kelas_id', $request->kelas_id))
            ],
            'jumlah_jam_per_minggu'  => ['required', 'integer', 'min:1', 'max:40'],
        ], [
            'guru_mata_pelajaran_id.unique' => 'Kebutuhan untuk guru×mapel ini di kelas tersebut sudah ada.',
        ]);

        KebutuhanMapelKelas::create($request->only(
            'kelas_id',
            'guru_mata_pelajaran_id',
            'jumlah_jam_per_minggu'
        ));

        return back()->with('toast_success', 'Kebutuhan mapel kelas berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $row = KebutuhanMapelKelas::findOrFail($id);

        $request->validate([
            'kelas_id'               => ['required', 'exists:kelas,id'],
            'guru_mata_pelajaran_id' => [
                'required',
                'exists:guru_mata_pelajaran,id',
                // tetap unik per (kelas, guru_mapel), kecuali baris sendiri
                Rule::unique('kebutuhan_mapel_kelas')
                    ->where(fn($q) => $q->where('kelas_id', $request->kelas_id))
                    ->ignore($row->id),
            ],
            'jumlah_jam_per_minggu'  => ['required', 'integer', 'min:1', 'max:40'],
        ], [
            'guru_mata_pelajaran_id.unique' => 'Kebutuhan untuk guru×mapel ini di kelas tersebut sudah ada.',
        ]);

        $row->update($request->only(
            'kelas_id',
            'guru_mata_pelajaran_id',
            'jumlah_jam_per_minggu'
        ));

        return back()->with('toast_success', 'Kebutuhan mapel kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        KebutuhanMapelKelas::findOrFail($id)->delete();
        return back()->with('toast_success', 'Kebutuhan mapel kelas berhasil dihapus.');
    }
}
