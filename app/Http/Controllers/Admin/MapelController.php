<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapelController extends Controller
{
    public function index()
    {
        $mataPelajaran = MataPelajaran::orderBy('nama_mata_pelajaran')
            ->Paginate(10)
            ->withQueryString(); // biar query string (mis. pencarian) ikut

        return view('admin.mapel.index', compact('mataPelajaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_mata_pelajaran' => 'required|string|max:255',
            'kode_mata_pelajaran' => 'required|string|max:50|unique:mata_pelajaran,kode_mata_pelajaran',
        ]);

        try {
            DB::beginTransaction();

            // Simpan data mata pelajaran saja
            MataPelajaran::create([
                'nama_mata_pelajaran' => $request->nama_mata_pelajaran,
                'kode_mata_pelajaran' => $request->kode_mata_pelajaran,
            ]);

            DB::commit();
            return redirect()->back()->with('toast_success', 'Mata pelajaran berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('toast_error', 'Gagal menyimpan data: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $mapel = MataPelajaran::findOrFail($id);
        return view('admin.mapel.edit', compact('mapel'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_mata_pelajaran' => 'required|string|max:255',
            'kode_mata_pelajaran' => 'required|string|max:50|unique:mata_pelajaran,kode_mata_pelajaran,' . $id,
        ]);

        try {
            DB::beginTransaction();

            $mapel = MataPelajaran::findOrFail($id);
            $mapel->update([
                'nama_mata_pelajaran' => $request->nama_mata_pelajaran,
                'kode_mata_pelajaran' => $request->kode_mata_pelajaran,
            ]);

            DB::commit();
            return redirect()->back()->with('toast_success', 'Data mata pelajaran berhasil diupdate');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('toast_error', 'Gagal mengupdate data: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $mapel = MataPelajaran::findOrFail($id);
            $mapel->delete();

            return redirect()->back()->with('toast_success', 'Mata pelajaran berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('toast_error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
