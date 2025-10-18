<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class JadwalGuruController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $jadwal = collect();

        if ($user && $user->guru) {
            // Eager load
            $user->guru->load([
                'guruMapel.mataPelajaran',
                'guruMapel.jadwalPelajaran.kelas',
                'guruMapel.jadwalPelajaran.ruangan',
            ]);

            // Map hari
            $mapHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];

            // Durasi per slot (0..12) untuk fallback
            $durasi = [30, 40, 40, 40, 30, 40, 40, 40, 50, 40, 40, 40, 40]; // jam-0 s.d. jam-12

            foreach ($user->guru->guruMapel as $gm) {
                foreach ($gm->jadwalPelajaran as $jp) {

                    // Normalisasi hari
                    $hariInt  = is_numeric($jp->hari) ? (int)$jp->hari : null;
                    $hariNama = $hariInt ? ($mapHari[$hariInt] ?? 'Lainnya') : (string)($jp->hari ?? 'Lainnya');
                    $hariOrder = $hariInt ?? 99;

                    // Ambil jam mulai/selesai (jika ada di DB)
                    $mulai   = $jp->jam_mulai ? Carbon::parse($jp->jam_mulai)->timezone('Asia/Jakarta') : null;
                    $selesai = $jp->jam_selesai ? Carbon::parse($jp->jam_selesai)->timezone('Asia/Jakarta') : null;

                    // Fallback: kalau jam_mulai/selesai kosong, hitung dari index jam (periode)
                    if ((!$mulai || !$selesai) && is_numeric($jp->jam)) {
                        $jamKe = (int)$jp->jam; // 0..12
                        $waktu = Carbon::now()->timezone('Asia/Jakarta')->setTime(7, 0, 0);
                        $start = null;
                        $end = null;
                        for ($i = 0; $i <= 12; $i++) {
                            $mulaiSlot = $waktu->copy();
                            $waktu->addMinutes($durasi[$i] ?? 40);
                            if ($i === $jamKe) {
                                $start = $mulaiSlot;
                                $end   = $waktu->copy();
                                break;
                            }
                        }
                        $mulai   = $mulai ?? $start;
                        $selesai = $selesai ?? $end;
                    }

                    // Label jam & kunci sort
                    if ($mulai && $selesai) {
                        $jamLabel = $mulai->format('H.i') . '–' . $selesai->format('H.i');
                        $jamSort  = $mulai->timestamp;
                    } else {
                        // Jika tetap tak bisa dihitung, pakai label sederhana
                        $jamLabel = is_numeric($jp->jam) ? ('Jam ke-' . (int)$jp->jam) : '-';
                        $jamSort  = is_numeric($jp->jam) ? ((int)$jp->jam) : 9999;
                    }

                    // Nama mapel (dukung dua kemungkinan kolom)
                    $mapelNama = $gm->mataPelajaran->nama_mata_pelajaran
                        ?? $gm->mataPelajaran->nama
                        ?? '-';

                    $jadwal->push([
                        'hari'        => $hariNama,
                        'hari_order'  => $hariOrder,
                        'jam'         => $jamLabel,
                        'jam_sort'    => $jamSort,
                        'mapel'       => $mapelNama,
                        'kelas'       => $jp->kelas->nama_kelas ?? '-',
                        'ruang'       => $jp->ruangan->nama ?? '-',
                    ]);
                }
            }

            // Sort: hari → jam
            $jadwal = $jadwal->sortBy([
                ['hari_order', 'asc'],
                ['jam_sort', 'asc'],
            ])->values();
        }

        return view('guru.jadwal.index', compact('jadwal'));
    }

    public function cetakPdf(Request $request)
    {
        $user = Auth::user();

        $jadwal = DB::table('jadwal_pelajaran as jp')
            ->select([
                'jp.hari',
                'jp.jam',
                'm.nama_mata_pelajaran as mapel',
                'k.nama_kelas as kelas',
                'r.nama as ruang',
            ])
            ->join('guru_mata_pelajaran as gmp', 'gmp.id', '=', 'jp.guru_mata_pelajaran_id') // sesuai migrasi
            ->join('gurus as g', 'g.id', '=', 'gmp.guru_id')
            ->leftJoin('mata_pelajaran as m', 'm.id', '=', 'gmp.mata_pelajaran_id')          // kolomnya nama_mata_pelajaran
            ->leftJoin('kelas as k', 'k.id', '=', 'jp.kelas_id')                              // kolomnya nama_kelas
            ->leftJoin('ruangan as r', 'r.id', '=', 'jp.ruangan_id')                          // tabel ruangan (tanpa s)
            ->where('g.user_id', $user->id)
            ->orderBy('jp.hari')
            ->orderBy('jp.jam')
            ->get();

        // mapping angka hari -> label (1=Senin .. 6=Sabtu)
        $mapHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
        $jadwal = $jadwal->map(function ($row) use ($mapHari) {
            $row->hari_label = $mapHari[$row->hari] ?? $row->hari;
            return $row;
        });

        $pdf = Pdf::loadView('guru.jadwal.pdf', [
            'jadwal'      => $jadwal,
            'guruNama'    => $user->guru->name,
            'dicetakPada' => now()->format('d M Y H:i'),
        ])
            ->setPaper('A4', 'portrait');

        return $pdf->download('jadwal-mengajar-' . $user->id . '.pdf');
    }
}
