<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JadwalPelajaran;
use App\Models\Kelas;
use App\Models\GuruMapel;   // <- model ke tabel guru_mata_pelajaran
use App\Models\Ruangan;
use App\Models\KebutuhanMapelKelas;
use App\Models\JadwalPelajaranHistory;
use App\Services\JadwalGeneratorLegacy;
use Illuminate\Support\Facades\Cache;


use Illuminate\Support\Facades\Log;

class PenjadwalanController extends Controller
{
    public function index(Request $request)
    {
        $kelasNama     = $request->query('kelas');
        $kelasTerpilih = null;
        $jadwal        = collect();

        if ($kelasNama) {
            $kelasTerpilih = Kelas::where('nama_kelas', $kelasNama)->first();
            if ($kelasTerpilih) {
                $jadwal = JadwalPelajaran::with([
                    'guruMapel.guru.user',
                    'guruMapel.mataPelajaran',
                    'ruangan'
                ])
                    ->where('kelas_id', $kelasTerpilih->id)
                    ->orderBy('hari')->orderBy('jam')
                    ->get();
            }
        }

        $listKelas = Kelas::orderBy('nama_kelas')->get();
        $guruMapel = GuruMapel::with(['guru.user', 'mataPelajaran'])->get()
            ->sortBy(function ($gm) {
                $mapel = $gm->mataPelajaran->nama_mata_pelajaran
                    ?? $gm->mataPelajaran->nama
                    ?? '';
                return mb_strtolower($mapel, 'UTF-8');
            })
            ->values();
        $ruangan   = Ruangan::orderBy('nama')->get();

        // ================== PREVIEW GLOBAL ==================
        $previewKey  = $request->query('preview_key') ?? session('ga_preview_key');
        $previewMeta = session('ga_preview_meta', []);             // meta info (fitness, dll)
        $previewRows = collect(session('ga_preview_rows', []));    // fallback (jika ada yg disimpan di session)

        // Jika ada key global, ambil SEMUA preview dari cache, lalu filter hanya untuk kelas yang dilihat
        if ($previewKey && $kelasTerpilih) {
            $rowsAll = Cache::get($previewKey, []);
            if (!empty($rowsAll)) {
                $filtered = array_values(array_filter($rowsAll, fn($r) => (int)$r['kelas_id'] === (int)$kelasTerpilih->id));
                $previewRows = collect($filtered);
            } else {
                // key ada tapi cache kosong → anggap tak ada preview
                $previewRows = collect();
            }
        }
        // ====================================================

        // Flag preview aktif utk kelas ini
        $hasPreview = $previewRows->isNotEmpty();

        // Flag jadwal final sudah tersimpan di DB utk kelas ini
        $hasFinal = false;
        if ($kelasTerpilih) {
            $hasFinal = JadwalPelajaran::where('kelas_id', $kelasTerpilih->id)->exists();
        }

        // Locked = sudah tersimpan dan tidak sedang preview (kalau sedang preview, kita tetap tampilkan preview)
        $isLocked = $hasFinal && !$hasPreview;

        // Hitung TERISI per guru_mata_pelajaran_id untuk kelas terpilih
        $terisiMap = [];
        $kekuranganMapel = collect();
        $totalSisa = 0;

        if ($kelasTerpilih) {
            if ($hasPreview) {
                // Dari PREVIEW (array session/cache)
                // Hanya hitung jenis MAPEL (abaikan ISTIRAHAT/EKSKUL/JAM 0)
                $terisiMap = $previewRows
                    ->filter(function ($r) {
                        $jenis = $r['jenis'] ?? 'MAPEL';
                        $jamKe = (int)($r['jam'] ?? -1);
                        return $jenis === 'MAPEL' && $jamKe !== 0;
                    })
                    ->groupBy('guru_mata_pelajaran_id')
                    ->map->count()
                    ->toArray();
            } else {
                // Dari DB final
                $terisiMap = JadwalPelajaran::where('kelas_id', $kelasTerpilih->id)
                    ->where('jenis', 'MAPEL')
                    ->where('jam', '!=', 0) // pastikan jam ke-0 tidak dihitung
                    ->selectRaw('guru_mata_pelajaran_id, COUNT(*) as terisi')
                    ->groupBy('guru_mata_pelajaran_id')
                    ->pluck('terisi', 'guru_mata_pelajaran_id')
                    ->toArray();
            }

            // Ambil kebutuhan mapel untuk kelas ini
            $kebutuhan = KebutuhanMapelKelas::with([
                'guruMapel.mataPelajaran:id,nama_mata_pelajaran',
                'guruMapel.guru:id,name' // atau 'guruMapel.guru.user:id,name' jika nama ada di users
            ])
                ->where('kelas_id', $kelasTerpilih->id)
                ->get();
            $kekuranganMapel = $kebutuhan->map(function ($k) use ($terisiMap) {
                $filled = (int) ($terisiMap[$k->guru_mata_pelajaran_id] ?? 0);
                $butuh  = (int) ($k->jumlah_jam_per_minggu ?? 0);
                $sisa   = $butuh - $filled;

                // Ambil nama guru: prioritas ke users.name, jatuh ke guru.name
                $namaGuru = data_get($k, 'guruMapel.guru.user.name')
                    ?? data_get($k, 'guruMapel.guru.name')
                    ?? '-';

                return [
                    'mapel'    => data_get($k, 'guruMapel.mataPelajaran.nama_mata_pelajaran', '-'),
                    'guru'     => $namaGuru,
                    'guru_id'  => data_get($k, 'guruMapel.guru_id'),
                    'butuh'    => $butuh,
                    'terisi'   => $filled,
                    'sisa'     => $sisa,
                ];
            })->sortByDesc('sisa')->values();

            $totalSisa = $kekuranganMapel->sum('sisa');
        }

        return view('admin.penjadwalan.index', compact(
            'listKelas',
            'kelasTerpilih',
            'guruMapel',
            'jadwal',
            'ruangan',
            'previewRows',
            'previewKey',
            'previewMeta',
            'hasPreview',
            'hasFinal',
            'isLocked',
            'kekuranganMapel',   // <— tambah
            'totalSisa',         // <— tambah
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hari'     => 'required|integer|min:1|max:5',     // 1=Senin .. 5=Jumat
            'jam'      => 'required|integer|min:0|max:12',    // jam-ke
            'kelas_id' => 'required|exists:kelas,id',

            // Mapel (jika diisi -> otomatis jadi jenis MAPEL)
            'guru_mata_pelajaran_id' => 'nullable|exists:guru_mata_pelajaran,id',
            'ruangan_id'             => 'nullable|exists:ruangan,id',

            // Kegiatan non-mapel (dipakai kalau guru_mata_pelajaran_id kosong)
            'jenis' => 'required_without:guru_mata_pelajaran_id|in:ISTIRAHAT,EKSKUL,UPACARA,KEGIATAN',
        ]);

        // Cek bentrok kelas
        if (JadwalPelajaran::where([
            'kelas_id' => $data['kelas_id'],
            'hari' => $data['hari'],
            'jam' => $data['jam']
        ])->exists()) {
            return back()->with('toast_error', 'Slot kelas pada hari & jam tersebut sudah terisi.');
        }

        // MODE MAPEL
        if (!empty($data['guru_mata_pelajaran_id'])) {
            $gmp   = GuruMapel::with('guru', 'mataPelajaran')->findOrFail($data['guru_mata_pelajaran_id']);
            $kelas = Kelas::findOrFail($data['kelas_id']);

            // Praktikum wajib pilih ruangan; Teori otomatis pakai ruangan kelas (kalau ada)
            $ruanganId = ($gmp->jenis ?? null) === 'PRAKTIKUM'
                ? ($data['ruangan_id'] ?? null)
                : ($kelas->ruangan_id ?? null);

            if (($gmp->jenis ?? null) === 'PRAKTIKUM' && empty($ruanganId)) {
                return back()->with('toast_error', 'Pilih ruangan untuk mapel praktik.');
            }

            // Cek be// Cek bentrok guru (berdasarkan guru_id, bukan gmp)
            $guruId = $gmp->guru->id ?? DB::table('guru_mata_pelajaran')
                ->where('id', $gmp->id)->value('guru_id');

            $guruBentrok = DB::table('jadwal_pelajaran as jp')
                ->join('guru_mata_pelajaran as gg', 'gg.id', '=', 'jp.guru_mata_pelajaran_id')
                ->where('jp.hari', $data['hari'])
                ->where('jp.jam',  $data['jam'])
                ->where('gg.guru_id', $guruId)
                ->exists();

            if ($guruBentrok) {
                return back()->with('toast_error', 'Guru sudah mengajar di slot tersebut.');
            }

            // Cek bentrok ruangan (jika ada)
            if ($ruanganId && JadwalPelajaran::where([
                'ruangan_id' => $ruanganId,
                'hari' => $data['hari'],
                'jam' => $data['jam']
            ])->exists()) {
                return back()->with('toast_error', 'Ruangan bentrok di slot tersebut.');
            }

            JadwalPelajaran::create([
                'hari'   => $data['hari'],
                'jam'    => $data['jam'],
                'kelas_id' => $data['kelas_id'],
                'guru_mata_pelajaran_id' => $gmp->id,
                'ruangan_id' => $ruanganId,
                'jenis'  => 'MAPEL',
            ]);

            return back()->with('toast_success', 'Mapel berhasil ditambahkan.');
        }

        // MODE KEGIATAN (non-mapel: Upacara/Senam/Literasi/Kebersihan/Ekskul/Istirahat)
        JadwalPelajaran::create([
            'hari'   => $data['hari'],
            'jam'    => $data['jam'],
            'kelas_id' => $data['kelas_id'],
            'guru_mata_pelajaran_id' => null,
            'ruangan_id' => $data['ruangan_id'] ?? null,
            'jenis'  => $data['jenis'],  // ISTIRAHAT / EKSKUL / UPACARA / KEGIATAN
        ]);

        return back()->with('toast_success', 'Kegiatan berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $jadwal = JadwalPelajaran::findOrFail($id);

        $data = $request->validate([
            'hari'     => 'required|integer|min:1|max:5',
            'jam'      => 'required|integer|min:0|max:12',
            'kelas_id' => 'required|exists:kelas,id',

            'guru_mata_pelajaran_id' => 'nullable|exists:guru_mata_pelajaran,id',
            'ruangan_id'             => 'nullable|exists:ruangan,id',

            'jenis' => 'required_without:guru_mata_pelajaran_id|in:ISTIRAHAT,EKSKUL,UPACARA,KEGIATAN',
        ]);

        // Cek bentrok kelas (kecuali dirinya)
        $existsKelas = JadwalPelajaran::where([
            'kelas_id' => $data['kelas_id'],
            'hari' => $data['hari'],
            'jam' => $data['jam']
        ])->where('id', '!=', $jadwal->id)->exists();
        if ($existsKelas) {
            return back()->with('toast_error', 'Slot kelas pada hari & jam tersebut sudah terisi.');
        }

        // MODE MAPEL
        if (!empty($data['guru_mata_pelajaran_id'])) {
            $gmp   = GuruMapel::with('guru', 'mataPelajaran')->findOrFail($data['guru_mata_pelajaran_id']);
            $kelas = Kelas::findOrFail($data['kelas_id']);

            $ruanganId = ($gmp->jenis ?? null) === 'PRAKTIKUM'
                ? ($data['ruangan_id'] ?? null)
                : ($kelas->ruangan_id ?? null);

            if (($gmp->jenis ?? null) === 'PRAKTIKUM' && empty($ruanganId)) {
                return back()->with('toast_error', 'Pilih ruangan untuk mapel praktik.');
            }

            // Bentrok guru (kecuali dirinya)
            $existsGuru = JadwalPelajaran::where([
                'guru_mata_pelajaran_id' => $gmp->id,
                'hari' => $data['hari'],
                'jam' => $data['jam']
            ])->where('id', '!=', $jadwal->id)->exists();
            if ($existsGuru) {
                return back()->with('toast_error', 'Guru sudah mengajar di slot tersebut.');
            }

            // Bentrok ruangan (kecuali dirinya)
            if ($ruanganId) {
                $existsRuangan = JadwalPelajaran::where([
                    'ruangan_id' => $ruanganId,
                    'hari' => $data['hari'],
                    'jam' => $data['jam']
                ])->where('id', '!=', $jadwal->id)->exists();
                if ($existsRuangan) {
                    return back()->with('toast_error', 'Ruangan bentrok di slot tersebut.');
                }
            }

            $jadwal->update([
                'hari'   => $data['hari'],
                'jam'    => $data['jam'],
                'kelas_id' => $data['kelas_id'],
                'guru_mata_pelajaran_id' => $gmp->id,
                'ruangan_id' => $ruanganId,
                'jenis'  => 'MAPEL',
            ]);

            return back()->with('toast_success', 'Mapel berhasil diperbarui.');
        }

        // MODE KEGIATAN
        $jadwal->update([
            'hari'   => $data['hari'],
            'jam'    => $data['jam'],
            'kelas_id' => $data['kelas_id'],
            'guru_mata_pelajaran_id' => null,
            'ruangan_id' => $data['ruangan_id'] ?? null,
            'jenis'  => $data['jenis'],
        ]);

        return back()->with('toast_success', 'Kegiatan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jadwal = JadwalPelajaran::findOrFail($id);

        $jadwal->delete();
        return back()->with('toast_success', 'Jadwal berhasil dihapus.');
    }

    private function buildSnapshotText(JadwalPelajaran $r): string
    {
        return sprintf(
            '[Kelas:%d] Hari:%d Jam:%d %s GM:%s R:%s',
            $r->kelas_id,
            $r->hari,
            $r->jam,
            $r->jenis,
            $r->guru_mata_pelajaran_id ?: '-',
            $r->ruangan_id ?: '-'
        );
    }

    public function isiJam0All()
    {
        $jam0 = [
            1 => 'UPACARA',
            2 => 'KEGIATAN',
            3 => 'KEGIATAN',
            4 => 'KEGIATAN',
            5 => 'KEGIATAN',
        ];

        $kelasIds = Kelas::pluck('id');
        $added = 0;
        $skipped = 0;

        foreach ($kelasIds as $kelasId) {
            foreach ($jam0 as $hari => $jenis) {
                $exists = DB::table('jadwal_pelajaran')
                    ->where('kelas_id', $kelasId)
                    ->where('hari', $hari)
                    ->where('jam', 0)
                    ->exists();

                if ($exists) {
                    $skipped++;
                    continue;
                }

                DB::table('jadwal_pelajaran')->insert([
                    'kelas_id'               => $kelasId,
                    'hari'                   => $hari,
                    'jam'                    => 0,
                    'jenis'                  => $jenis,
                    'guru_mata_pelajaran_id' => null,
                    'ruangan_id'             => null,
                    'created_at'             => now(),
                    'updated_at'             => now(),
                ]);

                $added++;
            }
        }

        return back()->with(
            'toast_success',
            "Jam ke-0 untuk semua kelas berhasil diproses. Ditambahkan: {$added}, sudah ada (skip): {$skipped}."
        );
    }

    public function isiEkskulAll(Request $request)
    {
        // Opsi: jika ?force=1 (timpa yang bentrok)
        $force = (bool)$request->boolean('force');

        $slots = [
            ['hari' => 3, 'jam' => 10],
            ['hari' => 3, 'jam' => 11],
            ['hari' => 3, 'jam' => 12],
            ['hari' => 5, 'jam' => 9],
            ['hari' => 5, 'jam' => 10],
        ];

        $kelasIds = Kelas::pluck('id')->all();

        $added = 0;
        $skipped = 0;
        $conflicts = 0;

        DB::transaction(function () use ($kelasIds, $slots, $force, &$added, &$skipped, &$conflicts) {
            foreach ($kelasIds as $kelasId) {
                foreach ($slots as $slot) {
                    // Kunci baris slot ini agar aman dari race condition paralel
                    $occupied = DB::table('jadwal_pelajaran')
                        ->where('kelas_id', $kelasId)
                        ->where('hari', $slot['hari'])
                        ->where('jam', $slot['jam'])
                        ->lockForUpdate()
                        ->first();

                    if ($occupied) {
                        // Sudah terisi (jenis apa pun)
                        if ($force) {
                            // Timpa: hapus dulu lalu isi EKSKUL
                            DB::table('jadwal_pelajaran')
                                ->where('id', $occupied->id)
                                ->delete();

                            DB::table('jadwal_pelajaran')->insert([
                                'kelas_id'               => $kelasId,
                                'hari'                   => $slot['hari'],
                                'jam'                    => $slot['jam'],
                                'jenis'                  => 'EKSKUL',
                                'guru_mata_pelajaran_id' => null,
                                'ruangan_id'             => null,
                                'created_at'             => now(),
                                'updated_at'             => now(),
                            ]);
                            $added++;
                        } else {
                            // Tidak force → lewati dan catat konflik
                            $conflicts++;
                            $skipped++;
                        }
                        continue;
                    }

                    // Slot kosong → isi EKSKUL
                    DB::table('jadwal_pelajaran')->insert([
                        'kelas_id'               => $kelasId,
                        'hari'                   => $slot['hari'],
                        'jam'                    => $slot['jam'],
                        'jenis'                  => 'EKSKUL',
                        'guru_mata_pelajaran_id' => null,
                        'ruangan_id'             => null,
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ]);
                    $added++;
                }
            }
        });

        $msg = $force
            ? "Ekskul diproses (timpa jika bentrok). Ditambahkan: {$added}, dilewati: {$skipped}, bentrok: {$conflicts}."
            : "Ekskul diproses. Ditambahkan: {$added}, dilewati: {$skipped} (karena slot sudah terisi).";

        return back()->with('toast_success', $msg);
    }

    public function reset(Request $request, Kelas $kelas)
    {
        $deleted = 0;

        DB::transaction(function () use ($kelas, &$deleted) {
            $batch = (string) Str::uuid();

            // ambil semua baris yg akan dihapus
            $rows = JadwalPelajaran::where('kelas_id', $kelas->id)->get();

            foreach ($rows as $row) {
                JadwalPelajaranHistory::create([
                    'batch_key'               => $batch,
                    'jadwal_pelajaran_id'     => $row->id,
                    'hari'                    => $row->hari,
                    'jam'                     => $row->jam,
                    'kelas_id'                => $row->kelas_id,
                    'guru_mata_pelajaran_id'  => $row->guru_mata_pelajaran_id,
                    'ruangan_id'              => $row->ruangan_id,
                    'jenis'                   => $row->jenis,
                    'aksi'                    => 'RESET',
                    'acted_by'                => Auth::id(),
                    'waktu_aksi'              => now(),
                    'snapshot_text'           => $this->buildSnapshotText($row),
                    'payload_lama'            => $row->toArray(),
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);
            }

            // HAPUS setelah tercatat di histori
            $deleted = DB::table('jadwal_pelajaran')
                ->where('kelas_id', $kelas->id)
                ->delete();
        });

        // (lanjut) … cache forget & log sisa seperti kode kamu

        return back()->with('toast_success', "Reset selesai. Dihapus {$deleted} slot untuk {$kelas->nama_kelas}.");
    }


    public function resetAll()
    {
        $total = 0;

        DB::transaction(function () use (&$total) {
            $batch = (string) Str::uuid();

            $rows = JadwalPelajaran::get();

            foreach ($rows as $row) {
                JadwalPelajaranHistory::create([
                    'batch_key'               => $batch,
                    'jadwal_pelajaran_id'     => $row->id,
                    'hari'                    => $row->hari,
                    'jam'                     => $row->jam,
                    'kelas_id'                => $row->kelas_id,
                    'guru_mata_pelajaran_id'  => $row->guru_mata_pelajaran_id,
                    'ruangan_id'              => $row->ruangan_id,
                    'jenis'                   => $row->jenis,
                    'aksi'                    => 'RESET',
                    'acted_by'                => Auth::id(),
                    'waktu_aksi'              => now(),
                    'snapshot_text'           => $this->buildSnapshotText($row),
                    'payload_lama'            => $row->toArray(),
                    'created_at'              => now(),
                    'updated_at'              => now(),
                ]);
            }

            $total = DB::table('jadwal_pelajaran')->delete();
        });

        return back()->with('toast_success', "Semua jadwal berhasil dihapus. Terhapus {$total} baris (terekam di histori).");
    }


    public function dashboard()
    {
        // Map hari EN -> angka 1..6
        $map = ['Monday' => 1, 'Tuesday' => 2, 'Wednesday' => 3, 'Thursday' => 4, 'Friday' => 5];
        $todayEn = now()->format('l');
        $hariNum = $map[$todayEn] ?? 1;

        $jadwalDB = JadwalPelajaran::with([
            'kelas',
            'guruMapel.guru',
            'guruMapel.mataPelajaran',
            'ruangan',
        ])
            ->where('hari', $hariNum)
            ->orderBy('kelas_id')->orderBy('jam')
            ->get();

        $jadwal = [];
        foreach ($jadwalDB as $item) {
            if (!$item->kelas) continue;
            $namaKelas = $item->kelas->nama_kelas;
            $waktu = $this->hitungJam($item->jam); // ['mulai','selesai']

            $jadwal[$namaKelas][$item->jam][] = [
                'mapel' => $item->guruMapel?->mataPelajaran?->nama ?? $item->jenis,
                'guru'  => $item->guruMapel?->guru?->user?->name
                    ?? $item->guruMapel?->guru?->nama
                    ?? '-',
                'ruang' => $item->ruangan?->nama ?? '-',
                'jam'   => $waktu['mulai'] . ' - ' . $waktu['selesai'],
            ];
        }

        return view('admin.dashboard', [
            'hari' => $todayEn,
            'jadwalHariIni' => $jadwal,
        ]);
    }

    /** Helper jam-ke -> waktu real (07:00 start) */
    private function hitungJam(int $jamKe): array
    {
        $waktu = now()->setTime(7, 0, 0);
        $durasi = [30, 40, 40, 40, 30, 40, 40, 40, 50, 40, 40, 40, 40]; // 0..12

        $mulai = null;
        $selesai = null;
        for ($i = 0; $i <= 12; $i++) {
            $mulaiSekarang = $waktu->copy();
            $waktu->addMinutes($durasi[$i] ?? 40);
            if ($i === $jamKe) {
                $mulai = $mulaiSekarang->format('H:i');
                $selesai = $waktu->format('H:i');
                break;
            }
        }
        return ['mulai' => $mulai ?? '00:00', 'selesai' => $selesai ?? '00:00'];
    }

    public function __construct(private JadwalGeneratorLegacy $generator) {}

    public function generate(Request $req, \App\Services\JadwalGeneratorLegacy $generator)
    {
        $mode = $req->input('mode', 'global');
        $ops  = ['alpha' => 10, 'beta' => 0.20];

        $kelasIds = collect($req->input('kelas_ids', []))
            ->map(fn($v) => (int)$v)->filter()->unique()->values()->all();

        if ($mode === 'kelas' && empty($kelasIds)) {
            return back()->with('warning', 'Pilih kelas untuk generate per kelas.');
        }

        // --- jalankan generator ---
        if ($mode === 'kelas') {
            $kelasFocusId = (int)$kelasIds[0];
            $preview = $generator->generatePreview($ops, $kelasFocusId);
            $rows = collect($preview['rows'] ?? [])
                ->filter(fn($r) => (int)$r['kelas_id'] === $kelasFocusId);
        } else {
            $preview = $generator->generatePreview($ops, null); // global
            $rows = collect($preview['rows'] ?? []);

            // >>> ambil kelas yang sedang dibuka (jika dikirim dari form)
            $kelasFocusId = $req->integer('current_kelas_id') ?: null;
        }

        // normalisasi & dedup
        $rows = $rows->map(function ($r) {
            $r['kelas_id'] = (int)$r['kelas_id'];
            $r['hari']     = (int)$r['hari'];
            $r['jam']      = (int)$r['jam'];
            return $r;
        })
            ->unique(fn($r) => $r['kelas_id'] . '-' . $r['hari'] . '-' . $r['jam'])
            ->values();

        // simpan preview
        $key = 'ga_preview:' . ($req->user()?->id ?? 'guest') . ':' . $mode . ':' . Str::uuid();
        Cache::put($key, $rows->all(), now()->addHours(5));

        // meta + simpan fokus agar “stay”
        $meta = ['mode' => $mode, 'kelas_ids' => $mode === 'kelas' ? [$kelasFocusId] : []];
        session(['ga_preview_key' => $key, 'ga_preview_meta' => $meta]);

        // tentukan nama kelas untuk redirect
        $namaKelas = null;
        if ($kelasFocusId) {
            $namaKelas = DB::table('kelas')->where('id', $kelasFocusId)->value('nama_kelas');
            // simpan juga ke session supaya index bisa fallback
            session(['kelas_terpilih_id' => $kelasFocusId]);
        } elseif ($req->filled('current_kelas_nama')) {
            // jika kamu prefer pakai nama langsung
            $namaKelas = $req->input('current_kelas_nama');
        }

        return redirect()
            ->route('admin.penjadwalan.index', [
                'kelas'       => $namaKelas,  // isi kalau ada; kalau null, halaman default
                'preview_key' => $key,
            ])
            ->with('previewRows', $rows->all())
            ->with('previewKey',  $key)
            ->with('previewMeta', $meta)
            ->with('success', $mode === 'kelas' ? 'Preview dibuat (per kelas).' : 'Preview dibuat (global).');
    }

    public function simpan(Request $req)
    {
        $req->validate(['cache_key' => 'required|string']);
        $cacheKey = (string) $req->input('cache_key');

        // 0) Ambil data dari cache (sekalian pull agar tidak dobel simpan)
        $rows = cache()->pull($cacheKey);
        if (!$rows) {
            return back()->with('error', 'Preview kadaluwarsa. Silakan generate ulang.');
        }

        // 1) Normalisasi -> array & bersihkan field non-DB
        $fieldsToUnset = ['_kelas', '_hari_label', '_mapel_label', '_guru_kode', '_guru_nama', '_mapel_nama', '_label'];
        $clean = collect($rows)
            ->map(fn($r) => (array) $r)
            ->map(function ($r) use ($fieldsToUnset) {
                foreach ($fieldsToUnset as $f) unset($r[$f]);

                // tipe & default aman
                $r['kelas_id']                = isset($r['kelas_id']) ? (int)$r['kelas_id'] : null;
                $r['hari']                    = isset($r['hari']) ? (int)$r['hari'] : null;
                $r['jam']                     = isset($r['jam']) ? (int)$r['jam'] : null;
                $r['guru_mata_pelajaran_id']  = $r['guru_mata_pelajaran_id'] ?? null;
                $r['ruangan_id']              = $r['ruangan_id'] ?? null;

                // jenis (UPPER) + default
                $r['jenis'] = strtoupper(trim($r['jenis'] ?? 'MAPEL'));

                // Jam-0 paksa utk kegiatan tertentu
                if (in_array($r['jenis'], ['UPACARA', 'KEGIATAN'], true)) {
                    $r['jam'] = 0;
                }
                return $r;
            })
            // buang baris yang tidak lengkap kunci uniknya
            ->filter(fn($r) => $r['kelas_id'] !== null && $r['hari'] !== null && $r['jam'] !== null)
            ->values();

        if ($clean->isEmpty()) {
            return back()->with('error', 'Data preview kosong/tidak valid.');
        }

        // 2) Kelas yang akan di-commit (bisa banyak kalau preview global)
        $kelasIds = $clean->pluck('kelas_id')->unique()->values()->all();

        // 3) Mapping GMP -> GURU (untuk validasi konflik guru)
        $gmpToGuru = DB::table('guru_mata_pelajaran')->pluck('guru_id', 'id')->all();

        // 4) Ambil slot guru & ruangan yang sudah terpakai oleh KELAS LAIN (di luar batch ini)
        $takenGuru = DB::table('jadwal_pelajaran as jp')
            ->join('guru_mata_pelajaran as gmp', 'gmp.id', '=', 'jp.guru_mata_pelajaran_id')
            ->whereNotIn('jp.kelas_id', $kelasIds)
            ->select('gmp.guru_id', 'jp.hari', 'jp.jam')
            ->get()
            ->map(fn($r) => "{$r->guru_id}-{$r->hari}-{$r->jam}")
            ->toArray();

        $takenRuang = DB::table('jadwal_pelajaran')
            ->whereNotIn('kelas_id', $kelasIds)
            ->whereNotNull('ruangan_id')
            ->select('ruangan_id', 'hari', 'jam')
            ->get()
            ->map(fn($r) => "{$r->ruangan_id}-{$r->hari}-{$r->jam}")
            ->toArray();

        // 5) Filter jenis yang boleh & cek bentrok guru/ruangan terhadap kelas lain
        $conflicts = [];
        $clean = $clean
            ->filter(fn($r) => in_array($r['jenis'], ['MAPEL', 'EKSKUL', 'UPACARA', 'KEGIATAN'], true))
            ->filter(function ($r) use ($gmpToGuru, $takenGuru, $takenRuang, &$conflicts) {
                // Cek bentrok GURU hanya untuk MAPEL
                if ($r['jenis'] === 'MAPEL' && !empty($r['guru_mata_pelajaran_id'])) {
                    $guruId = $gmpToGuru[$r['guru_mata_pelajaran_id']] ?? null;
                    if ($guruId) {
                        $key = "{$guruId}-{$r['hari']}-{$r['jam']}";
                        if (in_array($key, $takenGuru, true)) {
                            $conflicts[] = ['type' => 'guru', 'kelas_id' => $r['kelas_id'], 'hari' => $r['hari'], 'jam' => $r['jam'], 'guru_id' => $guruId];
                            return false; // skip baris bentrok
                        }
                    }
                }
                // Cek bentrok RUANGAN (jika ada ruangan_id)
                if (!empty($r['ruangan_id'])) {
                    $key = "{$r['ruangan_id']}-{$r['hari']}-{$r['jam']}";
                    if (in_array($key, $takenRuang, true)) {
                        $conflicts[] = ['type' => 'ruangan', 'kelas_id' => $r['kelas_id'], 'hari' => $r['hari'], 'jam' => $r['jam'], 'ruangan_id' => $r['ruangan_id']];
                        return false; // skip baris bentrok
                    }
                }
                return true;
            })
            ->values();

        // 6) DEDUPE per slot (kelas_id-hari-jam). Prioritas MAPEL > EKSKUL > UPACARA/KEGIATAN
        $priority = ['MAPEL' => 3, 'EKSKUL' => 2, 'UPACARA' => 1, 'KEGIATAN' => 1];
        $bySlot = [];
        foreach ($clean as $r) {
            $slotKey = $r['kelas_id'] . '-' . $r['hari'] . '-' . $r['jam'];
            if (!isset($bySlot[$slotKey]) || ($priority[$r['jenis']] ?? 0) > ($priority[$bySlot[$slotKey]['jenis']] ?? 0)) {
                $bySlot[$slotKey] = $r;
            }
        }
        $clean = collect(array_values($bySlot));

        // 7) SIMPAN atomik: hapus final LAMA untuk kelas2 dalam batch -> insert BARU
        DB::transaction(function () use ($kelasIds, $clean) {
            DB::table('jadwal_pelajaran')->whereIn('kelas_id', $kelasIds)->delete();

            if ($clean->isNotEmpty()) {
                $now = now();
                $payload = $clean->map(function ($r) use ($now) {
                    $r['created_at'] = $r['created_at'] ?? $now;
                    $r['updated_at'] = $now;
                    return $r;
                })->all();

                DB::table('jadwal_pelajaran')->insert($payload);
            }
        });

        // 8) Feedback
        if (!empty($conflicts)) {
            $list = collect($conflicts)->map(function ($c) {
                return $c['type'] === 'guru'
                    ? "Guru {$c['guru_id']} @ Hari {$c['hari']} Jam {$c['jam']}"
                    : "Ruangan {$c['ruangan_id']} @ Hari {$c['hari']} Jam {$c['jam']}";
            })->unique()->implode(', ');
            return back()
                ->with('success', 'Jadwal tersimpan (sebagian baris konflik di-skip).')
                ->with('warning', "Baris konflik yang di-skip: {$list}");
        }

        return back()->with('success', 'Jadwal tersimpan dan menggantikan jadwal lama (berdasarkan kelas dalam preview).');
    }


    public function cancelPreview(Request $request)
    {
        // Ambil key dari input atau dari session
        $key = $request->input('preview_key') ?? $request->session()->get('ga_preview_key');

        if (!$key) {
            return back()->with('info', 'Tidak ada preview aktif yang bisa dibatalkan.');
        }

        // Hapus batch preview dari cache (global, semua kelas)
        Cache::forget($key);

        // Bersihkan jejak di session
        $request->session()->forget([
            'ga_preview_key',
            'ga_preview_meta',
            'ga_preview_rows',
        ]);

        Log::info('GA Preview dibatalkan (global).', [
            'user_id' => optional($request->user())->id,
            'preview_key' => $key,
        ]);

        return back()->with('success', 'Preview global berhasil dibatalkan dan dikosongkan.');
    }
}
