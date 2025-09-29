@extends('layouts.kepalaSekolah')

@section('content')
<style>
    /* Kartu/sel tetap enak dilihat */
    .td-jadwal {
        vertical-align: middle;
    }

    .td-jadwal .fw-bold,
    .td-jadwal .small {
        color: #212529;
    }

    /* Palet pastel—harus sinkron dengan hashing di atas (0..9) */
    .bg-mapel-0 {
        background: #E3F2FD !important;
    }

    /* biru muda   */
    .bg-mapel-1 {
        background: #E8F5E9 !important;
    }

    /* hijau muda  */
    .bg-mapel-2 {
        background: #FFF3E0 !important;
    }

    /* oranye muda */
    .bg-mapel-3 {
        background: #F3E5F5 !important;
    }

    /* ungu muda   */
    .bg-mapel-4 {
        background: #E0F7FA !important;
    }

    /* cyan muda   */
    .bg-mapel-5 {
        background: #FCE4EC !important;
    }

    /* pink muda   */
    .bg-mapel-6 {
        background: #F1F8E9 !important;
    }

    /* hijau limau */
    .bg-mapel-7 {
        background: #FFFDE7 !important;
    }

    /* krem muda   */
    .bg-mapel-8 {
        background: #EDE7F6 !important;
    }

    /* ungu pucat  */
    .bg-mapel-9 {
        background: #E0E0E0 !important;
    }

    /* abu muda    */

    /* (Opsional) Biar non-mapel (kode lama pakai table-info) juga lembut */
    .td-jadwal.table-info {
        background: #CCE5FF !important;
    }

    /* mirip ekskul lembut */
</style>

<div class="container-fluid">
    {{-- Judul Halaman --}}
    <h1 class="mb-4">
        Penjadwalan Kelas:
        <span id="namaKelas">
            {{ $kelasTerpilih?->nama_kelas ?? request('kelas') ?? 'Belum dipilih' }}
        </span>
    </h1>

    {{-- Kontrol Pilih Kelas --}}
    <div class="row align-items-center mb-4 g-2">
        <div class="col-md-auto">
            <div class="btn-group">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    Pilih Kelas Lain
                </button>
                <ul class="dropdown-menu">
                    @foreach ($listKelas as $kelasItem)
                    <li>
                        <a class="dropdown-item {{ request('kelas') == $kelasItem->nama_kelas ? 'active' : '' }}"
                            href="{{ route('kepala_sekolah.penjadwalan.index', ['kelas' => $kelasItem->nama_kelas]) }}">
                            {{ $kelasItem->nama_kelas }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

    {{-- Tabel Jadwal --}}
    @if ($kelasTerpilih && isset($jadwal) && is_array($jadwal))
    <div class="table-responsive">
        @php
        // Siapkan palet kelas CSS untuk MAPEL (0..9)
        $PALET_KELAS = ['bg-mapel-0','bg-mapel-1','bg-mapel-2','bg-mapel-3','bg-mapel-4','bg-mapel-5','bg-mapel-6','bg-mapel-7','bg-mapel-8','bg-mapel-9'];
        $warnaMapel = $warnaMapel ?? []; // hormati jika sudah ada

        // Helper: ambil kelas dari nama mapel konsisten
        if (!function_exists('kelasMapel')) {
        function kelasMapel(?string $nama, array $kelasPalet): string {
        if (!$nama) return '';
        $key = mb_strtolower(trim($nama));
        $idx = abs(crc32($key)) % count($kelasPalet);
        return $kelasPalet[$idx]; // contoh: "bg-mapel-3"
        }
        }

        // Isi mapping untuk semua mapel yang muncul agar $warnaMapel[...] terdefinisi
        foreach (['Senin','Selasa','Rabu','Kamis','Jumat'] as $__h) {
        if (!isset($jadwal[$__h]) || !is_array($jadwal[$__h])) continue;
        foreach ($jadwal[$__h] as $__jam => $__item) {
        if (!is_array($__item)) continue;
        $jenis = $__item['jenis'] ?? 'MAPEL';
        if ($jenis === 'MAPEL') {
        $nama = $__item['mapel'] ?? null;
        if ($nama && !isset($warnaMapel[$nama])) {
        $warnaMapel[$nama] = kelasMapel($nama, $PALET_KELAS);
        }
        }
        }
        }
        @endphp

        @php
        // Pemetaan hari -> nomor, untuk akses cepat
        $mapHariNum = ['Senin'=>1,'Selasa'=>2,'Rabu'=>3,'Kamis'=>4,'Jumat'=>5];

        // Label kegiatan jam ke-0 per hari
        $jam0Labels = [
        1 => 'Upacara', // Senin
        2 => 'Kebersihan', // Selasa
        3 => 'Literasi Agama', // Rabu
        4 => 'Literasi Umum', // Kamis
        5 => 'Senam & Kebersihan' // Jumat
        ];
        @endphp

        <table class="table table-bordered table-striped text-center table-jadwal w-100">
            <colgroup>
                <col style="width: 10%;"> {{-- Jam --}}
                <col style="width: 18%;"> {{-- Senin --}}
                <col style="width: 18%;"> {{-- Selasa --}}
                <col style="width: 18%;"> {{-- Rabu --}}
                <col style="width: 18%;"> {{-- Kamis --}}
                <col style="width: 18%;"> {{-- Jumat --}}
            </colgroup>
            <thead class="table-light align-middle">
                <tr>
                    <th class="jam-kolom">Jam</th>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                    <th>{{ $hari }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($jam = 0; $jam <= 12; $jam++)
                    <tr @if ($jam===4 || $jam===8) class="table-warning fw-bold text-center" @endif>
                    {{-- Kolom Jam --}}
                    <td class="fw-normal">
                        {{ $jamWaktu[$jam]['jam'] ?? 'Jam Kosong' }}
                    </td>

                    {{-- Kolom Hari-hari --}}
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                    @php
                    $item = $jadwal[$hari][$jam] ?? null;
                    $nonMapel = isset($item['jenis']) && $item['jenis'] !== 'MAPEL';

                    // Tentukan class TD:
                    // - Baris istirahat (jam 4/8): tanpa warna khusus (sudah di-row warning)
                    // - Non-MAPEL: pakai table-info (lembut)
                    // - MAPEL: pakai kelas dari $warnaMapel[mapel]
                    $cellClass =
                    ($jam === 4 || $jam === 8) ? '' :
                    ($nonMapel ? 'table-info' :
                    ($item && !empty($item['mapel']) ? ($warnaMapel[$item['mapel']] ?? '') : '')
                    );
                    @endphp

                    <td class="td-jadwal {{ $cellClass }}">
                        @if ($jam === 4 || $jam === 8)
                        Istirahat
                        @elseif ($item)
                        @if ($nonMapel && $jam === 0)
                        {{-- Kegiatan Jam 0: label mengikuti hari --}}
                        <div class="fw-bold">
                            {{ $jam0Labels[$mapHariNum[$hari]] ?? 'Kegiatan Jam 0' }}
                        </div>
                        @elseif ($nonMapel)
                        {{-- Non-mapel di jam selain 0 (mis. EKSKUL/KEGIATAN lain) --}}
                        <div class="fw-bold">{{ $item['jenis'] }}</div>
                        @else
                        {{-- MAPEL biasa --}}
                        <div class="fw-bold">{{ $item['mapel'] }}</div>
                        <div class="small">{{ $item['guru'] ?: 'GURU KOSONG' }}</div>
                        <div class="text-muted small">
                            {{ $item['ruang'] ?: ($kelasTerpilih?->nama_kelas ?? 'RUANG KOSONG') }}
                        </div>
                        @endif
                        @else
                        <span class="text-muted">-</span>
                        @endif
                    </td>
                    @endforeach
                    </tr>
                    @endfor
            </tbody>
        </table>
    </div>
    @else
    <div class="alert alert-warning text-center">
        Silakan pilih kelas terlebih dahulu untuk melihat jadwal.
    </div>
    @endif
</div>
@endsection