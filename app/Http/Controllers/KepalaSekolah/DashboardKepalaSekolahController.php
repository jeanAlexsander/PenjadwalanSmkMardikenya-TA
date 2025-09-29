<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Jurusan;
use App\Models\MataPelajaran;
use App\Models\JadwalPelajaran;
use Illuminate\Support\Facades\Schema;


class DashboardKepalaSekolahController extends Controller
{
    public function index()
    {
        Carbon::setLocale('id');
        setlocale(LC_TIME, 'id_ID.UTF-8');

        $now  = now()->setTimezone('Asia/Jakarta');
        $hari = $this->hariNamaIndo($this->hariNum($now)); // 'Senin', 'Selasa', ...

        // Bentuk struktur jadwal lengkap (per kelas → per hari → per jam-ke)
        $jadwalLengkap = $this->getJadwalPelajaran();

        // Ambil hanya hari ini
        $jadwalHariIni = [];
        foreach ($jadwalLengkap as $kelas => $jadwalPerHari) {
            $jadwalHariIni[$kelas] = $jadwalPerHari[$hari] ?? [];
        }

        Log::info('Debug Dashboard', [
            'hari_now' => $hari,
            'kelas_terisi' => array_keys($jadwalHariIni),
            'jumlah_kelas_hari_ini' => count($jadwalHariIni),
        ]);

        return view('kepala_sekolah.dashboard.index', [
            'hari'          => $hari,
            'jadwalHariIni' => $jadwalHariIni,
            'totalMapel'    => MataPelajaran::count(),
            'totalKelas'    => Kelas::count(),
            'totalGuru'     => Guru::count(),
            'totalJurusan'  => Jurusan::count(),
        ]);
    }

    /**
     * Ambil seluruh jadwal -> struktur:
     * [nama_kelas][NamaHari]['Jam 0/1/..'] = ['jam' => '07:00–07:45', 'mapel'=>..., 'guru'=>..., 'ruang'=>...]
     */
    public function getJadwalPelajaran(): array
    {
        $rows = JadwalPelajaran::with([
            'kelas',
            'guruMapel.guru.user',
            'guruMapel.mataPelajaran',
            'ruangan',
        ])
            ->orderBy('kelas_id')
            ->orderBy('hari')      // 1..6
            ->orderBy('jam')       // 0..12
            ->get();

        $jadwal = [];

        foreach ($rows as $item) {
            $namaKelas = $item->kelas->nama_kelas ?? 'Tanpa Kelas';
            $hariNama  = $this->hariNamaIndo((int)($item->hari ?? 1)); // ubah 1..6 -> 'Senin'..'Sabtu'
            $jamKe     = (int)($item->jam ?? 0);

            // Hitung label waktu dari jam-ke
            $waktu = $this->hitungJam($jamKe); // ['mulai'=>'07:00','selesai'=>'07:45']
            $jamRange = "{$waktu['mulai']}–{$waktu['selesai']}";
            $jamKey   = "Jam {$jamKe}";

            // Ambil nama mapel/guru/ruang
            $mapel   = $item->guruMapel?->mataPelajaran?->nama_mata_pelajaran
                ?? $item->guruMapel?->mataPelajaran?->nama
                ?? ($item->jenis ?? '-');
            $guru    = $item->guruMapel?->guru?->name
                ?? $item->guruMapel?->guru?->name
                ?? '-';
            $ruangan = $item->ruangan?->nama
                ?? $item->kelas?->nama_kelas  // default ruang kelas utk teori (opsional)
                ?? '-';

            // Logging ringan bila ada kosong
            if ($mapel === '-' || $guru === '-' || $ruangan === '-') {
                Log::warning('Data kosong pada jadwal', [
                    'jadwal_id' => $item->id,
                    'kelas'     => $item->kelas?->nama_kelas,
                    'hari_num'  => $item->hari,
                    'jam_ke'    => $item->jam,
                    'gmp_id'    => $item->guru_mata_pelajaran_id,
                    'jenis'     => $item->jenis,
                ]);
            }

            $jadwal[$namaKelas][$hariNama][$jamKey] = [
                'jam'   => $jamRange,
                'mapel' => $mapel,
                'guru'  => $guru,
                'ruang' => $ruangan,
            ];
        }

        return $jadwal;
    }

    /** Hitung label waktu dari jam-ke (start 07:00, pola durasi 0..12) */
    private function hitungJam(int $jamKe): array
    {
        $waktu  = now()->setTime(7, 0, 0);
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
        return ['mulai' => $mulai ?? '00:00', 'selesai' => $selesai ?? '00:00'];
    }

    /** Konversi Carbon day ke angka 1..6 (Senin..Sabtu) */
    private function hariNum(Carbon $dt): int
    {
        // Carbon: Monday=1..Sunday=7
        $n = (int)$dt->isoWeekday(); // 1..7
        return min($n, 6); // kalau Minggu (7) -> 6/Sabtu, atau sesuaikan kebutuhan
    }

    /** 1..6 -> 'Senin'..'Sabtu' */
    private function hariNamaIndo(int $num): string
    {
        $map = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'];
        return $map[$num] ?? 'Senin';
    }
}
