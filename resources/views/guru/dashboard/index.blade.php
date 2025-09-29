@extends('layouts.main')
@section('content')
<!-- Informasi Umum -->
<h1 class="mb-4">Dashboard Guru</h1>
<div class="card border-0 shadow-sm">
    <div class="alert alert-light border-start border-4 border-success shadow-sm">
        <h5 class="mb-1">
            <i class="fas fa-info-circle me-2 text-success"></i>
            Selamat Datang, {{ Auth::user()->guru->name }} !
        </h5>
        <p class="mb-0">Senang bertemu kembali. Silakan gunakan fitur yang tersedia di menu sebelah kiri.</p>
    </div>

</div>

<div class="row">
    <!-- Jadwal Mengajar -->
    <div class="col-md-4 mb-4">
        <a href="{{ route('guru.jadwal.index') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 d-flex flex-row align-items-center p-3 bg-success text-white card-link">
                <div class="me-3">
                    <i class="fas fa-calendar-alt fa-2x"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Jadwal Mengajar</h5>
                    <p class="card-text fs-4 mb-0">{{ $jumlahJadwal }} Jadwal</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Kelas Diampu -->
    <div class="col-md-4 mb-4">
        <a href="{{ route('guru.profil.index') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 d-flex flex-row align-items-center p-3 bg-primary text-white card-link">
                <div class="me-3">
                    <i class="fas fa-users fa-2x"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Kelas Diampu</h5>
                    <p class="card-text fs-4 mb-0">{{ $kelasDiampu->count() }} Kelas</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Mata Pelajaran -->
    <div class="col-md-4 mb-4">
        <a href="{{ route('guru.profil.index') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 d-flex flex-row align-items-center p-3 bg-warning text-dark card-link">
                <div class="me-3">
                    <i class="fas fa-book fa-2x"></i>
                </div>
                <div>
                    <h5 class="card-title mb-0">Mata Pelajaran</h5>
                    <p class="card-text fs-4 mb-0">{{ $user->guru->guruMapel->count() }} Mapel</p>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- Jadwal Mengajar Hari Ini --}}
<div class="card shadow mb-4">
    <div class="card-header bg-dark text-white">
        <i class="fas fa-calendar-day me-2"></i> Jadwal Mengajar Hari Ini ({{ $hari }})
    </div>
    <div class="card-body">
        @if ($jadwalHariIni->isEmpty())
        <p class="text-muted text-center m-0">Tidak ada jadwal mengajar hari ini.</p>
        @else
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <colgroup>
                    <col style="width: 15%;"> <!-- Jam -->
                    <col style="width: 20%;"> <!-- Kelas -->
                    <col style="width: 40%;"> <!-- Mata Pelajaran -->
                    <col style="width: 25%;"> <!-- Ruangan -->
                </colgroup>
                <thead class="table-light">
                    <tr>
                        <th>Jam</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Ruangan</th>
                    </tr>
                </thead>
                <tbody>
                    @php $counter = 0; @endphp
                    @foreach ($jadwalHariIni as $item)
                    @php $counter++; @endphp
                    <tr>
                        <td>
                            @if (!empty($item['jam_mulai']) && !empty($item['jam_selesai']))
                            Pukul {{ \Carbon\Carbon::parse($item['jam_mulai'])->timezone('Asia/Jakarta')->format('H.i') }}
                            – {{ \Carbon\Carbon::parse($item['jam_selesai'])->timezone('Asia/Jakarta')->format('H.i') }} WIB
                            @else
                            {{ $item['jam'] ?? 'Jam Kosong' }}
                            @endif
                        </td>
                        <td>{{ $item['kelas'] ?? 'Kelas Kosong' }}</td>
                        <td>{{ $item['mapel'] ?? 'MAPEL KOSONG' }}</td>
                        <td>{{ $item['ruang'] ?? 'RUANG KOSONG' }}</td>
                    </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
        @endif
    </div>
</div>


@endsection