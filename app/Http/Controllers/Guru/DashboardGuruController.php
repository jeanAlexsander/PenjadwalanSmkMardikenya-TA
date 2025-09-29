<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardGuruController extends Controller
{
    public function index()
    {
        // Set locale & timezone langsung di sini
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8');

        $now  = now()->setTimezone('Asia/Jakarta');
        $hari = ucfirst($now->locale('id')->isoFormat('dddd')); // Senin, Selasa, dst

        $user = Auth::user();

        $jumlahJadwal   = 0;
        $kelasDiampu    = collect();
        $jadwalHariIni  = collect();

        if ($user && $user->guru) {
            $user->guru->load([
                'guruMapel.mataPelajaran',
                'guruMapel.jadwalPelajaran.kelas',
                'guruMapel.jadwalPelajaran.ruangan',
            ]);

            foreach ($user->guru->guruMapel as $gm) {
                $jumlahJadwal += $gm->jadwalPelajaran->count();

                foreach ($gm->jadwalPelajaran as $jadwal) {
                    if ($jadwal->kelas) {
                        $kelasDiampu->push($jadwal->kelas);
                    }

                    // cek hari pakai integer (lebih konsisten dibanding string nama)
                    if ((int)$jadwal->hari === $now->dayOfWeekIso) {
                        if ($jadwal->jam_mulai && $jadwal->jam_selesai) {
                            $mulai   = Carbon::parse($jadwal->jam_mulai)->timezone('Asia/Jakarta');
                            $selesai = Carbon::parse($jadwal->jam_selesai)->timezone('Asia/Jakarta');
                            $jamLabel = $mulai->format('H.i') . '–' . $selesai->format('H.i');
                            $jamSort  = $mulai->timestamp; // ⬅️ buat urutan
                        } else {
                            $jamIndex = is_numeric($jadwal->jam) ? (int)$jadwal->jam : null;
                            $p = $jamIndex !== null ? $this->hitungJam($jamIndex) : ['mulai' => null, 'selesai' => null];
                            $jamLabel = ($p['mulai'] && $p['selesai'])
                                ? str_replace(':', '.', $p['mulai']) . '–' . str_replace(':', '.', $p['selesai'])
                                : (is_int($jamIndex) ? 'Jam ke-' . $jamIndex : 'Jam Kosong');
                            $jamSort = $jamIndex ?? 9999; // fallback sort
                        }

                        $jadwalHariIni->push([
                            'jam'        => $jamLabel,
                            'kelas'      => $jadwal->kelas->nama_kelas ?? '-',
                            'mapel'      => $gm->mataPelajaran->nama_mata_pelajaran ?? '-',
                            'ruang'      => $jadwal->ruangan->nama ?? '-',
                            'jam_sort'   => $jamSort, // simpan kunci sort
                        ]);
                    }

                    // setelah loop selesai
                    $jadwalHariIni = $jadwalHariIni->sortBy('jam_sort')->values();
                }
            }


            $kelasDiampu = $kelasDiampu->unique('id')->values();

            // Urutkan berdasarkan jam mulai
            $jadwalHariIni = $jadwalHariIni->sortBy('jam_mulai')->values();
        }

        return view('guru.dashboard.index', compact(
            'user',
            'jumlahJadwal',
            'kelasDiampu',
            'jadwalHariIni',
            'hari'
        ));
    }

    /** Hitung waktu mulai–selesai dari jam ke- (0..12), start 07:00 */
    private function hitungJam(int $jamKe): array
    {
        $waktu  = now()->timezone('Asia/Jakarta')->setTime(7, 0, 0);
        $durasi = [45, 40, 40, 40, 30, 40, 40, 40, 35, 40, 40, 40, 40]; // 0..12

        $mulai = null;
        $selesai = null;
        for ($i = 0; $i <= 12; $i++) {
            $mulaiSekarang = $waktu->copy();
            $waktu->addMinutes($durasi[$i] ?? 40);
            if ($i === $jamKe) {
                $mulai   = $mulaiSekarang->format('H:i');
                $selesai = $waktu->format('H:i');
                break;
            }
        }
        return ['mulai' => $mulai, 'selesai' => $selesai];
    }

    /** Ubah hasil hitungJam jadi label "Pukul 07.00–07.40 WIB" */
    private function labelPukulDariJam(?int $jamIndex): ?string
    {
        if (!is_int($jamIndex)) return null;
        $p = $this->hitungJam($jamIndex);                // ['mulai'=>'07:00','selesai'=>'07:40']
        if (!$p['mulai'] || !$p['selesai']) return null;
        return 'Pukul ' . str_replace(':', '.', $p['mulai']) . '–' . str_replace(':', '.', $p['selesai']) . ' WIB';
    }
}
