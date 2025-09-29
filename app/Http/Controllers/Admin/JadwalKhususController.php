<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\JadwalKhusus;
use App\Models\Kelas;
use App\Models\Ruangan;

class JadwalKhususController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        // "Hari ini" di zona Asia/Jakarta
        $today = Carbon::now('Asia/Jakarta')->startOfDay();

        $jadwal = JadwalKhusus::with(['kelas', 'ruangan'])
            ->whereDate('tanggal', '>=', $today->toDateString())
            ->orderBy('tanggal', 'asc')
            // kalau ada kolom jam_mulai, boleh tambahkan biar urut dalam hari yang sama:
            // ->orderBy('jam_mulai', 'asc')
            ->paginate($perPage)
            ->withQueryString(); // bawa ?per_page / filter lain saat pindah halaman

        $kelasList   = Kelas::orderBy('nama_kelas', 'asc')->get();
        $ruanganList = Ruangan::orderBy('nama', 'asc')->get();

        return view('admin.jadwal_khusus.index', compact('jadwal', 'kelasList', 'ruanganList'));
    }

    public function store(Request $request)
    {
        Log::info('Data Masuk:', $request->all());

        $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'jam_mulai' => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after_or_equal:jam_mulai',
            'ruangan_id' => 'nullable|exists:ruangan,id',
            'keterangan' => 'nullable|string',
            'kelas_id' => 'nullable|array',
            'kelas_id.*' => 'exists:kelas,id',
        ]);

        $jadwal = JadwalKhusus::create([
            'nama_kegiatan' => $request->nama_kegiatan,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'ruangan_id' => $request->filled('ruangan_id') ? $request->ruangan_id : null,
            'keterangan' => $request->keterangan,
            'untuk_semua_kelas' => $request->has('untuk_semua_kelas'),
        ]);

        if (!$jadwal->untuk_semua_kelas && $request->kelas_id) {
            $jadwal->kelas()->attach($request->kelas_id);
        }

        return redirect()->route('admin.jadwal_khusus.index')->with('toast_success', 'Kegiatan berhasil ditambahkan.');
    }


    public function update(Request $request, $id)
    {
        try {
            $jadwal = JadwalKhusus::findOrFail($id);

            $request->validate([
                'nama_kegiatan' => 'required|string|max:255',
                'tanggal' => 'required|date',
                'jam_mulai' => 'nullable|date_format:H:i',
                'jam_selesai' => 'nullable|date_format:H:i|after_or_equal:jam_mulai',
                'ruangan_id' => 'nullable|exists:ruangan,id',
                'keterangan' => 'nullable|string',
                'kelas_id' => 'nullable|array',
                'kelas_id.*' => 'exists:kelas,id',
            ]);

            $jadwal->update([
                'nama_kegiatan' => $request->nama_kegiatan,
                'tanggal' => $request->tanggal,
                'jam_mulai' => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'ruangan_id' => $request->filled('ruangan_id') ? $request->ruangan_id : null,
                'keterangan' => $request->keterangan,
                'untuk_semua_kelas' => $request->has('untuk_semua_kelas'),
            ]);

            Log::info('Update berhasil untuk ID: ' . $id);
            Log::info('Data yang disimpan:', $request->all());

            if (!$jadwal->untuk_semua_kelas && $request->kelas_id) {
                $jadwal->kelas()->sync($request->kelas_id);
            } else {
                $jadwal->kelas()->detach();
            }

            return redirect()->route('admin.jadwal_khusus.index')->with('toast_success', 'Kegiatan berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Gagal update jadwal khusus: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan perubahan.');
        }
    }

    public function destroy($id)
    {
        $jadwal = JadwalKhusus::findOrFail($id);
        $jadwal->kelas()->detach(); // hapus relasi dulu
        $jadwal->delete();

        return redirect()->route('admin.jadwal_khusus.index')->with('toast_success', 'Kegiatan berhasil dihapus.');
    }
}
