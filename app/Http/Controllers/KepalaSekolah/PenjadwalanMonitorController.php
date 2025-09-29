<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\GuruMapel;
use App\Models\JadwalPelajaran;
use App\Models\Ruangan;

class PenjadwalanMonitorController extends Controller
{
    public function index(Request $request)
    {
        $kelasNama = $request->query('kelas');

        // Dropdown
        $listKelas = Kelas::orderBy('nama_kelas')->get();

        // Kelas terpilih
        $kelasTerpilih = $kelasNama
            ? Kelas::where('nama_kelas', $kelasNama)->first()
            : null;

        // Ambil jadwal untuk kelas terpilih
        $jadwalDB = $kelasTerpilih
            ? JadwalPelajaran::with(['guruMapel.guru', 'guruMapel.mataPelajaran', 'ruangan', 'kelas'])
            ->where('kelas_id', $kelasTerpilih->id)
            ->orderBy('hari')   // kolom yang ada
            ->orderBy('jam')    // 0..12
            ->get()
            : collect();

        // Map hari int -> string
        $mapHari  = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat'];
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        // Susun struktur [$hariNama][$jamKe] = {mapel,guru,ruang,jenis}
        $jadwal = [];
        foreach ($jadwalDB as $item) {
            $hariNama = is_numeric($item->hari) ? ($mapHari[(int)$item->hari] ?? 'Lainnya') : ($item->hari ?? 'Lainnya');
            if (!in_array($hariNama, $hariList, true)) continue;

            $jamKe = is_numeric($item->jam) ? (int)$item->jam : 99;
            if ($jamKe < 0 || $jamKe > 12) continue;

            // Siapkan nilai dasar
            $mapelNama = $item->guruMapel?->mataPelajaran?->nama_mata_pelajaran
                ?? $item->guruMapel?->mataPelajaran?->nama
                ?? null;
            $guruNama  = $item->guruMapel?->guru?->name ?? null;
            $ruangNama = $item->ruangan?->nama ?? null;
            $namaKls   = $item->kelas?->nama_kelas ?? null;

            // Tampilkan sesuai JENIS
            if (in_array($item->jenis, ['UPACARA', 'EKSKUL', 'KEGIATAN'], true)) {
                $labelKegiatan = match ($item->jenis) {
                    'UPACARA' => 'UPACARA',
                    'EKSKUL'  => 'EKSKUL',
                    default   => 'KEGIATAN',
                };

                $jadwal[$hariNama][$jamKe] = [
                    'mapel' => $labelKegiatan,
                    'guru'  => '-', // atau isi pembina default jika ada mapping
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

        // Label jam 0..12 (pakai titik)
        $jamWaktu = [
            0  => ['jam' => '07.00–07.30'],
            1  => ['jam' => '07.30–08.10'],
            2  => ['jam' => '08.10–08.50'],
            3  => ['jam' => '08.50–09.30'],
            4  => ['jam' => '09.30–10.00'], // Istirahat
            5  => ['jam' => '10.00–10.40'],
            6  => ['jam' => '10.40–11.20'],
            7  => ['jam' => '11.20–12.00'],
            8  => ['jam' => '12.00–12.50'], // Istirahat
            9  => ['jam' => '12.50–13.30'],
            10 => ['jam' => '13.30–14.10'],
            11 => ['jam' => '14.10–14.40'],
            12 => ['jam' => '14.40–15.20'],
        ];

        // Isi slot kosong untuk semua hari & jam 0..12
        for ($jam = 0; $jam <= 12; $jam++) {
            foreach ($hariList as $hari) {
                if (!isset($jadwal[$hari][$jam])) {
                    $jadwal[$hari][$jam] = null;
                }
            }
        }

        // (opsional) mirror array jika view butuh
        $listPelajaran = [];
        for ($jam = 0; $jam <= 12; $jam++) {
            foreach ($hariList as $hari) {
                $listPelajaran[$hari][$jam] = $jadwal[$hari][$jam];
            }
        }

        // Data lain untuk form
        $guruMapel = GuruMapel::with(['guru.user', 'mataPelajaran'])->get();
        $ruangan   = Ruangan::all();

        return view('kepala_sekolah.penjadwalan.index', compact(
            'listKelas',
            'kelasTerpilih',
            'guruMapel',
            'jadwal',
            'ruangan',
            'jamWaktu',
            'listPelajaran'
        ));
    }
}
