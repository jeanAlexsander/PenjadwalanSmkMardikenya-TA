<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jurusan;

class JurusanController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10); // opsional: ?per_page=25

        $dataJurusan = Jurusan::orderBy('nama_jurusan', 'asc')
            ->paginate($perPage)
            ->withQueryString(); // bawa query saat pindah halaman

        return view('admin.jurusan.index', compact('dataJurusan'));
    }

    public function create()
    {
        return view('admin.jurusan.create');
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'nama_jurusan' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        Jurusan::create([
            'nama_jurusan' => $request->nama_jurusan,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.jurusan.index')
            ->with('toast_success', 'Jurusan berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:255',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $jurusan = Jurusan::findOrFail($id);
        $jurusan->update([
            'nama_jurusan' => $request->nama_jurusan,
            'keterangan' => $request->keterangan,
        ]);


        return redirect()->route('admin.jurusan.index')
            ->with('toast_success', 'Jurusan berhasil diupdate.');
    }


    public function destroy($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        $jurusan->delete();

        return redirect()->route('admin.jurusan.index')
            ->with('toast_success', 'Jurusan berhasil dihapus.');
    }
}
