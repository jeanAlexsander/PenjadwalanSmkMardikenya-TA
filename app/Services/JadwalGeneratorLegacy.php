<?php
// app/Services/GeneticAlgorithm/JadwalGeneratorLegacy.php
namespace App\Services;

use App\Services\GeneticAlgorithm\Legacy\GAEngine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class JadwalGeneratorLegacy
{
    public function __construct(private GAEngine $engine) {}

    /**
     * === MODE PREVIEW ===
     * Jalankan GA, TAPI TIDAK menyimpan ke DB.
     * Hasil disimpan sementara di cache 30 menit.
     * Return:
     *  - key  : string cache key (untuk "Simpan Jadwal")
     *  - rows : baris siap insert (dipakai render preview)
     *  - meta : info fitness & total baris
     *
     * @param array $ops parameter GA
     * @param int|null $onlyKelasId jika diisi, hanya keluarkan rows utk kelas ini
     */
    public function generatePreview(array $ops = [], ?int $onlyKelasId = null): array
    {
        // 1) Kelas & hari
        $kelasList = DB::table('kelas')->orderBy('nama_kelas')->pluck('nama_kelas', 'id')->all();
        if (empty($kelasList)) {
            $kelasList = [
                0 => '10 Kuliner',
                1 => '10 Busana',
                2 => '11 Kuliner',
                3 => '11 Busana',
                4 => '12 Kuliner',
                5 => '12 Busana'
            ];
        }
        $hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $this->engine->setKelas(array_values($kelasList));
        $this->engine->setHari($hari);

        // 2) Bentuk $mapel dari DB
        $rows = DB::table('kebutuhan_mapel_kelas as kmk')
            ->join('guru_mata_pelajaran as gmp', 'gmp.id', '=', 'kmk.guru_mata_pelajaran_id')
            ->join('mata_pelajaran as mp', 'mp.id', '=', 'gmp.mata_pelajaran_id')
            ->join('gurus as g', 'g.id', '=', 'gmp.guru_id')
            ->join('kelas as k', 'k.id', '=', 'kmk.kelas_id')
            ->selectRaw('
            k.nama_kelas as kelas,
            mp.nama_mata_pelajaran as mapel,
            g.id as guru,
            gmp.jenis as jenis,
            kmk.jumlah_jam_per_minggu as jam
        ')
            ->orderBy('k.nama_kelas')
            ->get();

        $mapel = [];
        foreach ($rows as $r) {
            $mapel[$r->kelas][] = [
                'nama'       => $r->mapel,
                'guru'       => (int)$r->guru,
                'jam'        => (int)$r->jam,
                'is_praktik' => strtoupper((string)$r->jenis) === 'PRAKTIKUM',
            ];
        }

        // 3) Jalankan GA
        $result  = $this->engine->run($mapel, $ops);
        $jadwal  = $result['jadwal'];
        $fitness = $result['fitness'] ?? 0;

        // 4) Prefetch ID untuk mapping FK
        $kelasIds = DB::table('kelas')->pluck('id', 'nama_kelas')->all();
        $mapelIds = DB::table('mata_pelajaran')->pluck('id', 'nama_mata_pelajaran')->all();
        $guruIds  = DB::table('gurus')->pluck('id', 'id')->all();
        $gmpRows  = DB::table('guru_mata_pelajaran')->select('id', 'guru_id', 'mata_pelajaran_id')->get();
        $gmpIndex = [];
        $gmpById  = []; // <-- tambahkan: map gmp_id -> guru_id (buat cek bentrok guru)
        foreach ($gmpRows as $gmp) {
            $gmpIndex[$gmp->guru_id][$gmp->mata_pelajaran_id] = $gmp->id;
            $gmpById[(int)$gmp->id] = (int)$gmp->guru_id; // <-- new
        }

        // (NEW) Kumpulan slot terpakai dari KELAS LAIN → dipakai hanya saat per-kelas
        $forbiddenGuruSet  = [];
        $forbiddenRuangSet = [];
        if ($onlyKelasId !== null) {
            $occupiedGuru = DB::table('jadwal_pelajaran as jp')
                ->join('guru_mata_pelajaran as gmp', 'gmp.id', '=', 'jp.guru_mata_pelajaran_id')
                ->where('jp.kelas_id', '<>', $onlyKelasId)
                ->select('gmp.guru_id', 'jp.hari', 'jp.jam')
                ->get()
                ->map(fn($r) => "{$r->guru_id}-{$r->hari}-{$r->jam}")
                ->all();

            $occupiedRuang = DB::table('jadwal_pelajaran')
                ->where('kelas_id', '<>', $onlyKelasId)
                ->whereNotNull('ruangan_id')
                ->select('ruangan_id', 'hari', 'jam')
                ->get()
                ->map(fn($r) => "{$r->ruangan_id}-{$r->hari}-{$r->jam}")
                ->all();

            $forbiddenGuruSet  = array_fill_keys($occupiedGuru, true);
            $forbiddenRuangSet = array_fill_keys($occupiedRuang, true);
        }

        $jam0 = [
            'Senin'  => ['jenis' => 'UPACARA',  'label' => 'Upacara'],
            'Selasa' => ['jenis' => 'KEGIATAN', 'label' => 'Kebersihan'],
            'Rabu'   => ['jenis' => 'KEGIATAN', 'label' => 'Literasi Keagamaan'],
            'Kamis'  => ['jenis' => 'KEGIATAN', 'label' => 'Literasi Umum'],
            'Jumat'  => ['jenis' => 'KEGIATAN', 'label' => 'Senam & Kebersihan'],
        ];

        $kelasNamesById  = array_flip($kelasIds);
        $targetKelasIds  = $onlyKelasId ? [$onlyKelasId] : array_values($kelasIds);

        // 5) Konversi ke rows siap insert
        $rowsToInsert = [];
        $slotSet = [];
        $priority = ['MAPEL' => 3, 'EKSKUL' => 2, 'UPACARA' => 1, 'KEGIATAN' => 1];

        $push = function (array $r) use (&$rowsToInsert, &$slotSet, $priority) {
            $r['kelas_id'] = (int)$r['kelas_id'];
            $r['hari']     = (int)$r['hari'];
            $r['jam']      = (int)$r['jam'];
            $r['jenis']    = strtoupper(trim($r['jenis'] ?? 'MAPEL'));
            if (in_array($r['jenis'], ['UPACARA', 'KEGIATAN'], true)) {
                $r['jam'] = 0;
            }
            $key = $r['kelas_id'] . '-' . $r['hari'] . '-' . $r['jam'];
            if (!isset($slotSet[$key])) {
                $slotSet[$key] = $r;
            } else {
                $exist = $slotSet[$key];
                $pNew  = $priority[$r['jenis']]     ?? 0;
                $pOld  = $priority[$exist['jenis']] ?? 0;
                if ($pNew > $pOld) $slotSet[$key] = $r;
            }
        };

        // === GA output -> MAPEL/EKSKUL/ISTIRAHAT ===
        foreach ($jadwal as $j) {
            $isNon   = in_array($j['mapel'], ['ISTIRAHAT', 'EKSKUL'], true);
            $kelasId = $kelasIds[$j['kelas']] ?? null;
            if (!$kelasId) continue;
            if ($onlyKelasId !== null && $kelasId !== $onlyKelasId) continue;

            $mapelId = $isNon ? null : ($mapelIds[$j['mapel']] ?? null);
            $guruId  = $isNon ? null : ($guruIds[$j['guru']] ?? null);
            $gmpId   = null;

            if (!$isNon) {
                if (!$mapelId || !$guruId) continue;
                $gmpId = $gmpIndex[$guruId][$mapelId] ?? null;
                if (!$gmpId) continue;
            }

            $row = [
                'kelas_id'               => $kelasId,
                'hari'                   => $this->mapHari($j['hari']),
                'jam'                    => (int)$j['jam'],
                'guru_mata_pelajaran_id' => $gmpId,
                'ruangan_id'             => null,
                'jenis'                  => $isNon ? strtoupper($j['mapel']) : 'MAPEL',
                'created_at'             => now(),
                'updated_at'             => now(),
                '_kelas'       => $j['kelas'],
                '_hari_label'  => $j['hari'],
                '_mapel_label' => $j['mapel'],
                '_guru_kode'   => $j['guru'] ?? null,
            ];

            if ($row['jenis'] === 'ISTIRAHAT') {
                continue;
            }

            // (NEW) CEK bentrok saat PER-KELAS: guru/ruangan sudah dipakai kelas lain?
            if ($onlyKelasId !== null) {
                if ($row['jenis'] === 'MAPEL' && $row['guru_mata_pelajaran_id']) {
                    $guruIdReal = $gmpById[$row['guru_mata_pelajaran_id']] ?? null;
                    if ($guruIdReal) {
                        $gKey = "{$guruIdReal}-{$row['hari']}-{$row['jam']}";
                        if (isset($forbiddenGuruSet[$gKey])) {
                            continue; // slot ini bentrok dengan jadwal kelas lain -> skip
                        }
                    }
                }
                if (!empty($row['ruangan_id'])) {
                    $rKey = "{$row['ruangan_id']}-{$row['hari']}-{$row['jam']}";
                    if (isset($forbiddenRuangSet[$rKey])) {
                        continue; // bentrok ruangan -> skip
                    }
                }
            }

            $push($row);
        }

        // === Tambah JAM-0 (UPACARA/KEGIATAN) ===
        foreach ($targetKelasIds as $kId) {
            $kelasNama = $kelasNamesById[$kId] ?? '-';
            foreach ($jam0 as $hariLabel => $info) {
                $hariNum = $this->mapHari($hariLabel);
                $row = [
                    'kelas_id'               => (int)$kId,
                    'hari'                   => (int)$hariNum,
                    'jam'                    => 0,
                    'guru_mata_pelajaran_id' => null,
                    'ruangan_id'             => null,
                    'jenis'                  => strtoupper($info['jenis']),
                    'created_at'             => now(),
                    'updated_at'             => now(),
                    '_kelas'       => $kelasNama,
                    '_hari_label'  => $hariLabel,
                    '_mapel_label' => $info['label'],
                    '_guru_kode'   => null,
                ];
                $push($row);
            }
        }

        // akhir
        $rowsToInsert = array_values($slotSet);

        // 6) Cache sementara (30 menit)
        $cacheKey = 'ga_preview:' . (Auth::id() ?? 'guest') . ':' . Str::uuid();
        Cache::put($cacheKey, $rowsToInsert, now()->addMinutes(30));

        return [
            'key'  => $cacheKey,
            'rows' => $rowsToInsert,
            'meta' => ['fitness' => $fitness, 'total' => count($rowsToInsert)],
        ];
    }


    /**
     * === MODE SIMPAN ===
     * Ambil hasil dari cache (preview) → hapus jadwal lama kelas terkait → insert ke DB.
     */
    public function persist(string $cacheKey): void
    {
        $rowsFromCache = Cache::pull($cacheKey);
        if (!$rowsFromCache || !is_array($rowsFromCache)) {
            throw new \RuntimeException('Data preview tidak ditemukan / sudah kedaluwarsa. Silakan generate ulang.');
        }

        // 1) Bersihkan & normalisasi
        $clean = collect($rowsFromCache)->map(function ($r) {
            // Buang field preview (non-DB)
            unset($r['_kelas'], $r['_hari_label'], $r['_mapel_label'], $r['_guru_kode']);

            // Normalisasi tipe & nilai wajib
            $r['kelas_id'] = (int)($r['kelas_id'] ?? 0);
            $r['hari']     = (int)($r['hari'] ?? 0);
            $r['jam']      = (int)($r['jam'] ?? 0);
            $r['jenis']    = strtoupper(trim($r['jenis'] ?? 'MAPEL'));

            // Jam-0: pastikan jam = 0
            if (in_array($r['jenis'], ['UPACARA', 'KEGIATAN'], true)) {
                $r['jam'] = 0;
            }

            // Nullable FK
            $r['guru_mata_pelajaran_id'] = $r['guru_mata_pelajaran_id'] ?? null;
            $r['ruangan_id']             = $r['ruangan_id'] ?? null;

            // Timestamps
            $now = now();
            $r['created_at'] = $r['created_at'] ?? $now;
            $r['updated_at'] = $now;

            return $r;
        })
            // 2) Buang jenis yang tidak perlu disimpan
            ->filter(fn($r) => in_array($r['jenis'], ['MAPEL', 'EKSKUL', 'UPACARA', 'KEGIATAN'], true))
            ->values();

        // 3) Dedupe per slot (kelas-hari-jam) → prioritas
        $priority = ['MAPEL' => 3, 'EKSKUL' => 2, 'UPACARA' => 1, 'KEGIATAN' => 1];
        $bySlot = [];
        foreach ($clean as $r) {
            $key = $r['kelas_id'] . '-' . $r['hari'] . '-' . $r['jam'];
            if (!isset($bySlot[$key]) || ($priority[$r['jenis']] ?? 0) > ($priority[$bySlot[$key]['jenis']] ?? 0)) {
                $bySlot[$key] = $r;
            }
        }
        $final = collect(array_values($bySlot));

        // Safety: kalau kosong ya tidak usah apa-apa
        if ($final->isEmpty()) {
            throw new \RuntimeException('Tidak ada baris yang valid untuk disimpan.');
        }

        DB::transaction(function () use ($final) {
            // 4) Hapus jadwal lama untuk kelas yang terlibat
            $kelasIds = $final->pluck('kelas_id')->unique()->values()->all();
            DB::table('jadwal_pelajaran')->whereIn('kelas_id', $kelasIds)->delete();

            // 5) Insert payload final
            DB::table('jadwal_pelajaran')->insert($final->all());

            // 6) History jika ada tabelnya
            if (DB::getSchemaBuilder()->hasTable('jadwal_pelajaran_history')) {
                $history = $final->map(function ($r) {
                    $r['aksi'] = 'GENERATE';
                    $r['waktu_aksi'] = now();
                    return $r;
                });
                DB::table('jadwal_pelajaran_history')->insert($history->all());
            }
        });
    }

    /**
     * === MODE LAMA (langsung simpan) ===
     * Kalau masih mau dipakai, biarkan method run() ini. Tapi kalau semua
     * harus preview dulu, kamu bisa hapus method ini.
     */
    public function run(array $ops = []): array
    {
        $preview = $this->generatePreview($ops);
        // langsung simpan:
        $this->persist($preview['key']);
        return ['schedule' => $preview['rows'], 'meta' => ['fitness' => $preview['meta']['fitness'] ?? 0]];
    }

    private function mapHari(string $hari): int
    {
        return match ($hari) {
            'Senin' => 1,
            'Selasa' => 2,
            'Rabu' => 3,
            'Kamis' => 4,
            'Jumat' => 5,
            default => 1
        };
    }
}
