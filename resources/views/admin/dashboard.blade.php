@extends('layouts.admin')

@section('content')

<h1 class="mb-4">Dashboard Admin</h1>

@if (Auth::check())
<p class="mb-4">Selamat datang, {{ Auth::user()->role  }}!</p>
@else
<p class="mb-4">Selamat datang, Admin (belum login)</p>
@endif

{{-- Kotak Statistik --}}
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ route('admin.mapel.index') }}" class="text-decoration-none">
            <div class="card text-white bg-primary shadow h-100 card-link">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-book fa-2x me-3"></i>
                    <div>
                        <h5 class="card-title mb-1">Total Mapel</h5>
                        <p class="card-text fs-4 mb-0">{{ $totalMapel }}</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ route('admin.kelas.index') }}" class="text-decoration-none">
            <div class="card text-white bg-success shadow h-100 card-link">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-school fa-2x me-3"></i>
                    <div>
                        <h5 class="card-title mb-1">Total Kelas</h5>
                        <p class="card-text fs-4 mb-0">{{ $totalKelas }}</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ route('admin.guru.index') }}" class="text-decoration-none">
            <div class="card text-white bg-warning shadow h-100 card-link">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-chalkboard-teacher fa-2x me-3"></i>
                    <div>
                        <h5 class="card-title mb-1">Total Guru</h5>
                        <p class="card-text fs-4 mb-0">{{ $totalGuru }}</p>
                    </div>
                </div>
            </div>
        </a>
    </div>

    <div class="col-12 col-sm-6 col-lg-3">
        <a href="{{ route('admin.jurusan.index') }}" class="text-decoration-none">
            <div class="card text-white bg-danger shadow h-100 card-link">
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-code-branch fa-2x me-3"></i>
                    <div>
                        <h5 class="card-title mb-1">Total Jurusan</h5>
                        <p class="card-text fs-4 mb-0">{{ $totalJurusan }}</p>
                    </div>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Jadwal Hari Ini --}}
<div class="card shadow mb-4">
    <div class="card-header bg-dark text-white">
        <i class="fas fa-calendar-day me-2"></i> Jadwal Hari Ini ({{ $hari }})
    </div>
    <div class="card-body">
        @forelse ($jadwalHariIni as $namaKelas => $listPelajaran)
        @php
        $kelasKosong = collect($listPelajaran)->filter(function ($item) {
        return !empty($item['mapel']) || !empty($item['guru']) || !empty($item['ruang']);
        })->isEmpty();
        @endphp

        <div class="mb-5">
            <h5 class="fw-bold">{{ $namaKelas }}</h5>
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center">
                    <colgroup>
                        <col style="width: 15%;">
                        <col style="width: 35%;">
                        <col style="width: 30%;">
                        <col style="width: 20%;">
                    </colgroup>
                    <thead class="table-light">
                        <tr>
                            <th>Jam</th>
                            <th>Mata Pelajaran</th>
                            <th>Guru</th>
                            <th>Ruangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($kelasKosong)
                        <tr class="table-warning text-center fw-bold">
                            <td colspan="4">Belum ada data yang tersedia.</td>
                        </tr>
                        @else
                        @php
                        // label Jam 0 per HARI (pakai variabel $hari yang sudah kamu set di header)
                        $jam0Labels = [
                        'Senin' => 'Upacara',
                        'Selasa' => 'Kebersihan',
                        'Rabu' => 'Literasi Keagamaan',
                        'Kamis' => 'Literasi Umum',
                        'Jumat' => 'Senam & Kebersihan',
                        ];
                        @endphp

                        @foreach ($listPelajaran as $item)
                        @php
                        // Deteksi Jam-0 yang tersimpan sebagai KEGIATAN/UPACARA
                        $jenis = $item['jenis'] ?? null; // kalau kamu kirimkan dari controller
                        $jamKe = $item['jam_ke'] ?? null; // kalau kamu kirimkan dari controller
                        $mapel = $item['mapel'] ?? null;

                        $isJam0 = (in_array($jenis, ['UPACARA','KEGIATAN'], true) && (int)$jamKe === 0)
                        || (in_array($mapel, ['UPACARA','KEGIATAN'], true) && (int)($jamKe ?? 0) === 0);

                        $isEkskul = ($jenis === 'EKSKUL') || ($mapel === 'EKSKUL');

                        $mapelDisp = $isJam0
                        ? ($jam0Labels[$hari] ?? 'Kegiatan Jam 0')
                        : ($isEkskul ? 'Ekstrakurikuler' : ($mapel ?? 'MAPEL KOSONG'));

                        $guruDisp = ($isJam0 || $isEkskul)
                        ? '-'
                        : ($item['guru'] ?? 'GURU KOSONG');

                        $ruangDisp = $item['ruang'] ?? 'RUANG KOSONG';
                        @endphp

                        <tr>
                            <td>{{ $item['jam'] ?? 'Jam Kosong' }}</td>
                            <td>{{ $mapelDisp }}</td>
                            <td>{{ $guruDisp }}</td>
                            <td>{{ $ruangDisp }}</td>
                        </tr>

                        {{-- Istirahat setelah jam ke-4 dan ke-7 (pakai urutan baris tampil) --}}
                        @if ($loop->iteration == 4 || $loop->iteration == 7)
                        <tr class="table-warning fw-bold">
                            <td colspan="4">ISTIRAHAT</td>
                        </tr>
                        @endif
                        @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
        @empty
        <p class="text-muted text-center m-0">Belum ada jadwal untuk hari ini.</p>
        @endforelse

    </div>
</div>

@endsection