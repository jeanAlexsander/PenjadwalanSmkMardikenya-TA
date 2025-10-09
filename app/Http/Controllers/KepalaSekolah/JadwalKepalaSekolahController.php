<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JadwalKepalaSekolahController extends Controller
{
    public function index()
    {
        $user   = Auth::user();                 // harus role 'kepala_sekolah'
        $jadwal = collect();
        $context = 'kepala_sekolah';            // untuk badge/heading di view

        if ($user && $user->guru) {
            // Eager load relasi yang sama seperti Guru
            $user->guru->load([
                'guruMapel.mataPelajaran',
                'guruMapel.jadwalPelajaran.kelas',
                'guruMapel.jadwalPelajaran.ruangan',
            ]);

            // Peta hari & durasi slot (fallback)
            $mapHari = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
            $durasi  = [45, 40, 40, 40, 30, 40, 40, 40, 35, 40, 40, 40, 40]; // jam-0..12

            foreach ($user->guru->guruMapel as $gm) {
                foreach ($gm->jadwalPelajaran as $jp) {
                    // Normalisasi hari
                    $hariInt   = is_numeric($jp->hari) ? (int)$jp->hari : null;
                    $hariNama  = $hariInt ? ($mapHari[$hariInt] ?? 'Lainnya') : (string)($jp->hari ?? 'Lainnya');
                    $hariOrder = $hariInt ?? 99;

                    // Ambil jam mulai/selesai (timezone Asia/Jakarta)
                    $mulai   = $jp->jam_mulai   ? Carbon::parse($jp->jam_mulai)->timezone('Asia/Jakarta')   : null;
                    $selesai = $jp->jam_selesai ? Carbon::parse($jp->jam_selesai)->timezone('Asia/Jakarta') : null;

                    // Fallback hitung dari index jam (periode)
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
                                $end = $waktu->copy();
                                break;
                            }
                        }
                        $mulai   = $mulai   ?? $start;
                        $selesai = $selesai ?? $end;
                    }

                    // Label jam + kunci sort
                    if ($mulai && $selesai) {
                        $jamLabel = $mulai->format('H.i') . '–' . $selesai->format('H.i');
                        $jamSort  = $mulai->timestamp;
                    } else {
                        $jamLabel = is_numeric($jp->jam) ? ('Jam ke-' . (int)$jp->jam) : '-';
                        $jamSort  = is_numeric($jp->jam) ? ((int)$jp->jam) : 9999;
                    }

                    // Nama mapel (dukung 2 kemungkinan kolom)
                    $mapelNama = $gm->mataPelajaran->nama_mata_pelajaran
                        ?? $gm->mataPelajaran->nama
                        ?? '-';

                    $jadwal->push([
                        'hari'       => $hariNama,
                        'hari_order' => $hariOrder,
                        'jam'        => $jamLabel,
                        'jam_sort'   => $jamSort,
                        'mapel'      => $mapelNama,
                        'kelas'      => $jp->kelas->nama_kelas ?? '-',
                        'ruang'      => $jp->ruangan->nama ?? '-',
                    ]);
                }
            }

            // Urutkan: hari → jam
            $jadwal = $jadwal->sortBy([
                ['hari_order', 'asc'],
                ['jam_sort', 'asc'],
            ])->values();
        }

        // Reuse view guru, tambahkan $context untuk badge
        return view('kepala_sekolah.jadwal.index', compact('jadwal'));
    }
}
