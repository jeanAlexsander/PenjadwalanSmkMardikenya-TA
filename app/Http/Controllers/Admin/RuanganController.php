<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RuanganController extends Controller
{
    // Tampilkan halaman data ruangan
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        $ruangan = Ruangan::orderBy('nama', 'asc')
            ->paginate($perPage)
            ->withQueryString(); // bawa ?per_page (atau query lain) saat pindah halaman

        return view('admin.ruangan.index', compact('ruangan'));
    }

    // Simpan data ruangan baru

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|unique:ruangan,nama',
            'kapasitas' => 'nullable|integer|min:1',
            'keterangan' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->with('toast_error', 'Nama ruangan sudah digunakan!')
                ->withInput();
        }

        Ruangan::create([
            'nama' => $request->nama,
            'kapasitas' => $request->kapasitas,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('toast_success', 'Ruangan berhasil ditambahkan.');
    }


    // Update data ruangan
    public function update(Request $request, $id)
    {
        $ruangan = Ruangan::findOrFail($id);

        $request->validateWithBag('editRuangan', [
            'nama' => 'required|unique:ruangan,nama,' . $ruangan->id,
            'kapasitas' => 'nullable|integer|min:1',
            'keterangan' => 'nullable|string',
        ], [
            'nama.unique' => 'Nama ruangan sudah digunakan.',
        ]);

        $ruangan->update([
            'nama' => $request->nama,
            'kapasitas' => $request->kapasitas,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->back()->with('toast_success', 'Ruangan berhasil diubah.');
    }


    // Hapus ruangan
    public function destroy($id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $ruangan->delete();

        return redirect()->route('admin.ruangan.index')
            ->with('toast_success', 'Data ruangan berhasil dihapus.');
    }
}
