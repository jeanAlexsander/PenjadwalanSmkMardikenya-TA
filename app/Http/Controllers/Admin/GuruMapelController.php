<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GuruMapel;
use App\Models\Guru;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GuruMapelController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10); // optional: ganti via ?per_page=20

        $guruMapel = GuruMapel::with(['guru', 'mataPelajaran'])
            ->orderBy('id', 'desc')
            ->paginate($perPage)
            ->withQueryString(); // biar query (search/filter/per_page) ikut

        // Dropdown tetap full (tanpa paginate)
        $guruList  = Guru::orderBy('name', 'asc')->get();
        $mapelList = MataPelajaran::orderBy('nama_mata_pelajaran', 'asc')->get();

        return view('admin.guru_mapel.index', compact('guruMapel', 'guruList', 'mapelList'));
    }

    public function store(Request $request)
    {
        try {
            // Validasi request
            $validated = $request->validate([
                'guru_id' => 'required|exists:gurus,id',
                'mapel_id' => 'required|exists:mata_pelajaran,id',
                'jenis' => 'required|in:TEORI,PRAKTIKUM',
            ], [
                'guru_id.required' => 'Guru harus dipilih.',
                'mapel_id.required' => 'Mata pelajaran harus dipilih.',
                'jenis.required' => 'Jenis pembelajaran harus dipilih.',
            ]);

            DB::beginTransaction();

            // Simpan data Guru Mapel
            GuruMapel::create([
                'guru_id' => $validated['guru_id'],
                'mata_pelajaran_id' => $validated['mapel_id'],
                'jenis' => $validated['jenis'],
            ]);

            DB::commit();

            return redirect()->route('admin.guru-mapel.index')
                ->with('toast_success', 'Guru Mapel berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Validasi gagal → tampil toast merah + tetap buka modal
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('toast_error', 'Periksa kembali inputan Anda.')
                ->with('tampilModalTambahGuruMapel', true);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('toast_error', 'Terjadi kesalahan saat menambahkan guru mapel: ' . $e->getMessage());
        }
    }


    public function update(Request $request, $id)
    {
        $guruMapel = GuruMapel::findOrFail($id);

        $validated = $request->validate([
            'guru_id'             => 'required|exists:gurus,id',            // cek nama tabelnya: 'gurus' atau 'guru'
            'mata_pelajaran_id'   => 'required|exists:mata_pelajaran,id',   // <- ganti ini
            'jenis'               => 'required|in:TEORI,PRAKTIKUM',
        ]);

        DB::transaction(function () use ($guruMapel, $validated) {
            $guruMapel->update([
                'guru_id'            => $validated['guru_id'],
                'mata_pelajaran_id'  => $validated['mata_pelajaran_id'],   // <- pakai ini
                'jenis'              => $validated['jenis'],
            ]);
        });

        return redirect()
            ->route('admin.guru-mapel.index')
            ->with('toast_success', 'Data guru mapel berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $guruMapel = GuruMapel::findOrFail($id);

        DB::beginTransaction();

        try {
            $guruMapel->delete();

            DB::commit();

            return redirect()->route('admin.guru-mapel.index')->with('toast_success', 'Guru Mapel berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('toast_error', 'Terjadi kesalahan saat menghapus guru mapel.');
        }
    }
}
