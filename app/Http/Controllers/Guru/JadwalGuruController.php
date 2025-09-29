<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
            $durasi = [45, 40, 40, 40, 30, 40, 40, 40, 35, 40, 40, 40, 40]; // jam-0 s.d. jam-12

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
}
