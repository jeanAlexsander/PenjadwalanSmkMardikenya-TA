<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\JadwalPelajaran;

class CetakController extends Controller
{
    public function index(Request $request)
    {
        $kelasFilter = $request->query('kelas');
        $jadwalLengkap = $this->getJadwalPelajaran();
        $jamWaktu = $this->getWaktuJam();
        $dataCetak = [];

        if ($kelasFilter) {
            if ($kelasFilter !== 'semua') {
                if (isset($jadwalLengkap[$kelasFilter])) {
                    $dataCetak[] = [
                        'kelas' => $kelasFilter,
                        'jadwal' => $jadwalLengkap[$kelasFilter],
                    ];
                }
            } else {
                foreach ($jadwalLengkap as $kelas => $jadwal) {
                    $dataCetak[] = [
                        'kelas' => $kelas,
                        'jadwal' => $jadwal,
                    ];
                }
            }
        }

        return view('kepala_sekolah.cetak.index', compact('dataCetak', 'jamWaktu', 'jadwalLengkap'));
    }

    public function getJadwalPelajaran()
    {
        $semuaKelas = Kelas::all(); // ambil semua kelas
        $hasil = [];

        // mapping angka → nama hari
        $mapHari  = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        foreach ($semuaKelas as $kelas) {
            // URUTKAN pakai kolom yang ADA: hari + jam (0..12)
            $jadwalDB = JadwalPelajaran::with(['guruMapel.guru', 'guruMapel.mataPelajaran', 'ruangan', 'kelas'])
                ->where('kelas_id', $kelas->id)
                ->orderBy('hari')
                ->orderBy('jam')  // ← ganti dari jam_mulai
                ->get();

            $jadwal = [];

            foreach ($jadwalDB as $item) {
                // Normalisasi hari ke string
                $hariNama = is_numeric($item->hari) ? ($mapHari[(int)$item->hari] ?? 'Lainnya') : ($item->hari ?? 'Lainnya');
                if (!in_array($hariNama, $hariList, true)) continue;

                // Tentukan index jam (0..12)
                $jamKe = is_numeric($item->jam) ? (int)$item->jam : $this->cariJamKe($item->jam_mulai);
                if ($jamKe < 0 || $jamKe > 12) continue;

                // Nilai dasar
                $mapelNama = $item->guruMapel?->mataPelajaran?->nama_mata_pelajaran
                    ?? $item->guruMapel?->mataPelajaran?->nama
                    ?? null;
                $guruNama  = $item->guruMapel?->guru?->name ?? null;
                $ruangNama = $item->ruangan?->nama ?? null;
                $namaKls   = $item->kelas?->nama_kelas ?? null;

                // Tampilkan sesuai jenis
                if (in_array($item->jenis, ['UPACARA', 'EKSKUL', 'KEGIATAN'], true)) {
                    $label = match ($item->jenis) {
                        'UPACARA' => 'UPACARA',
                        'EKSKUL'  => 'EKSKUL',
                        default   => 'KEGIATAN',
                    };

                    $jadwal[$hariNama][$jamKe] = [
                        'mapel' => $label,
                        'guru'  => '-', // atau pembina default kalau ada
                        'ruang' => $ruangNama ?: ($namaKls ?: 'RUANG KOSONG'),
                        'jenis' => $item->jenis,
                    ];
                } else {
                    // MAPEL biasa
                    $jadwal[$hariNama][$jamKe] = [
                        'mapel' => $mapelNama ?: 'MAPEL KOSONG',
                        'guru'  => $guruNama  ?: 'GURU KOSONG',
                        'ruang' => $ruangNama ?: ($namaKls ?: 'RUANG KOSONG'),
                        'jenis' => 'MAPEL',
                    ];
                }
            }

            // Isi kekosongan jam 0..12 untuk tiap hari agar grid rapi
            for ($jam = 0; $jam <= 12; $jam++) {
                foreach ($hariList as $h) {
                    if (!isset($jadwal[$h][$jam])) {
                        $jadwal[$h][$jam] = null;
                    }
                }
            }

            // Simpan ke hasil dengan key nama kelas
            $hasil[$kelas->nama_kelas] = $jadwal;
        }

        return $hasil;
    }



    public function exportPdf(Request $request)
    {
        $kelasFilter = $request->query('kelas');
        $jadwalLengkap = $this->getJadwalPelajaran();
        $jamWaktu = $this->getWaktuJam(); // ⬅️ Tambah ini juga

        $dataCetak = [];

        if ($kelasFilter && $kelasFilter !== 'semua') {
            if (isset($jadwalLengkap[$kelasFilter])) {
                $dataCetak[] = [
                    'kelas' => $kelasFilter,
                    'jadwal' => $jadwalLengkap[$kelasFilter],
                ];
            }
        } else {
            foreach ($jadwalLengkap as $kelas => $jadwal) {
                $dataCetak[] = [
                    'kelas' => $kelas,
                    'jadwal' => $jadwal,
                ];
            }
        }

        $kepalaSekolah = (object)[
            'nama' => 'Gama Oktavina, S.Pd',
        ];

        $pdf = Pdf::loadView('kepala_sekolah.cetak.pdf', compact('dataCetak', 'kepalaSekolah', 'jamWaktu'))
            ->setPaper('A4', 'portrait');

        return $pdf->stream('jadwal-pelajaran-' . ($kelasFilter ?? 'semua') . '.pdf');
    }

    private function getWaktuJam()
    {
        return [
            0  => '07:00 - 07:45',
            1  => '07:45 - 08:25',
            2  => '08:25 - 09:05',
            3  => '09:05 - 09:45',
            4  => '09:45 - 10:15', // Istirahat
            5  => '10:15 - 10:55',
            6  => '10:55 - 11:35',
            7  => '11:35 - 12:15',
            8  => '12:15 - 12:50', // Istirahat
            9  => '12:50 - 13:30',
            10 => '13:30 - 14:10',
            11 => '14:10 - 14:50',
            12 => '14:50 - 15:30',
        ];
    }

    private function cariJamKe($jamMulai)
    {
        if (!$jamMulai) return 99;
        // Normalisasi ke 'H:i'
        $str = substr((string)$jamMulai, 0, 5); // '07:00' dari '07:00:00' atau '07:00'
        return match ($str) {
            '07:00' => 0,
            '07:45' => 1,
            '08:25' => 2,
            '09:05' => 3,
            '09:45' => 4,
            '10:15' => 5,
            '10:55' => 6,
            '11:35' => 7,
            '12:15' => 8,
            '12:50' => 9,
            '13:30' => 10,
            '14:10' => 11,
            '14:50' => 12,
            default  => 99,
        };
    }
}
