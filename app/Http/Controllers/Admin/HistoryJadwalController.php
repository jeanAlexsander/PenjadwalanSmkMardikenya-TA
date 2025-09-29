<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPelajaranHistory;
use App\Models\Kelas;
use App\Models\Ruangan;
use App\Models\GuruMapel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HistoryJadwalController extends Controller
{
    // Daftar batch histori
    public function index(Request $req)
    {
        $perPage = (int) $req->query('per_page', 10);

        // Query agregat per batch
        $base = JadwalPelajaranHistory::query()
            ->selectRaw('batch_key, MAX(waktu_aksi) AS waktu_aksi, MIN(aksi) AS aksi, COUNT(*) AS total')
            ->whereNotNull('batch_key')
            ->groupBy('batch_key');

        // Bungkus sebagai subquery => baru urut & paginate
        $batches = DB::query()
            ->fromSub($base, 'b')
            ->orderByDesc('waktu_aksi')
            ->paginate($perPage)
            ->withQueryString(); // bawa ?per_page atau parameter lain

        return view('admin.histori.index', compact('batches'));
    }

    // Detail satu batch (kirim mapping nama ke Blade)

    public function show(Request $req, string $batch)
    {
        $perPage = (int) $req->query('per_page', 15);

        // Data utama (paginate)
        $rows = JadwalPelajaranHistory::where('batch_key', $batch)
            ->orderBy('kelas_id')
            ->orderBy('hari')
            ->orderBy('jam')
            ->paginate($perPage)
            ->withQueryString();

        if ($rows->total() === 0) {
            return back()->with('error', 'Batch tidak ditemukan atau kosong.');
        }

        // Lookup nama kelas & ruangan (map: [id => nama])
        $kelasNames   = Kelas::pluck('nama_kelas', 'id');
        $ruanganNames = Ruangan::pluck('nama', 'id');

        // Ambil hanya gmp_id yang tampil di HALAMAN INI (hemat query)
        $pageItems = $rows->getCollection(); // Collection item halaman aktif
        $gmpIds = $pageItems->pluck('guru_mata_pelajaran_id')->filter()->unique();

        $gmpNames = collect();
        if ($gmpIds->isNotEmpty()) {
            $gmpNames = GuruMapel::with([
                'mataPelajaran:id,nama_mata_pelajaran',
                'guru:id,name',
            ])
                ->whereIn('id', $gmpIds)
                ->get()
                ->mapWithKeys(function ($gmp) {
                    $mapel = $gmp->mataPelajaran?->nama_mata_pelajaran ?? 'MAPEL';
                    $guru  = $gmp->guru?->name ?? '(guru belum diatur)';
                    return [$gmp->id => "{$mapel} – {$guru}"];
                });
        }

        return view('admin.histori.show', compact(
            'rows',        // paginator
            'batch',
            'kelasNames',
            'ruanganNames',
            'gmpNames'
        ));
    }


    // Export PDF per batch (grid Jam × Hari per kelas)
    public function exportPdf(string $batch)
    {
        $rows = JadwalPelajaranHistory::where('batch_key', $batch)
            ->orderBy('kelas_id')
            ->orderBy('hari')
            ->orderBy('jam')
            ->get();

        if ($rows->isEmpty()) {
            return back()->with('error', 'Batch tidak ditemukan atau kosong.');
        }

        // Ambil waktu aksi terakhir dalam batch
        $latestJakarta = Carbon::parse($rows->max('waktu_aksi'))
            ->setTimezone('Asia/Jakarta')
            ->format('d M Y H:i');

        $meta = [
            'batch' => $batch,
            'waktu' => $latestJakarta,
        ];

        // Lookup nama kelas & ruangan
        $kelasNames   = Kelas::pluck('nama_kelas', 'id');
        $ruanganNames = Ruangan::pluck('nama', 'id');

        // Ambil label Mapel – Guru untuk setiap guru_mata_pelajaran_id
        $gmpIds = $rows->pluck('guru_mata_pelajaran_id')->filter()->unique();

        $gmpNames = collect();
        if ($gmpIds->isNotEmpty()) {
            $gmpNames = GuruMapel::with([
                'mataPelajaran:id,nama_mata_pelajaran',
                'guru:id,name',
            ])
                ->whereIn('id', $gmpIds)
                ->get()
                ->mapWithKeys(function ($gmp) {
                    $mapel = $gmp->mataPelajaran?->nama_mata_pelajaran ?? 'MAPEL';
                    $guru  = $gmp->guru?->name ?? '(guru belum diatur)';
                    return [$gmp->id => "{$mapel} – {$guru}"];
                });
        }

        $pdf = Pdf::loadView('admin.histori.pdf', compact(
            'rows',
            'meta',
            'kelasNames',
            'ruanganNames',
            'gmpNames'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("histori-jadwal-{$batch}.pdf");
    }
}
