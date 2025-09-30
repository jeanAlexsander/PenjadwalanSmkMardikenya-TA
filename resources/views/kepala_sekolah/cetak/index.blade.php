@extends('layouts.kepalaSekolah')

@section('content')
<h1 class="mb-4">Cetak Jadwal Pelajaran</h1>
<div class="container mt-4">
    {{-- Form Pilih Kelas untuk Cetak PDF --}}
    <form action="{{ route('kepala_sekolah.cetak.index') }}" method="GET" class="row g-3 mb-4">
        <div class="col-md-2">
            <select name="kelas" class="form-select" required onchange="this.form.submit()">
                <option value="" disabled {{ request('kelas') ? '' : 'selected' }}>Pilih Kelas</option>

                @foreach ($jadwalLengkap as $kelas => $jadwal)
                <option value="{{ $kelas }}" {{ request('kelas') == $kelas ? 'selected' : '' }}>{{ $kelas }}</option>
                @endforeach

                <option value="semua" {{ request('kelas') == 'semua' ? 'selected' : '' }}>Semua Kelas</option>
            </select>

        </div>

        {{-- Tombol Cetak PDF --}}
        <div class="col-auto">
            <a href="{{ route('kepala_sekolah.cetak.pdf', ['kelas' => request('kelas', 'semua')]) }}"

                class="btn btn-danger"
                target="_blank">
                <i class="fas fa-file-pdf me-1"></i> Cetak PDF
            </a>
        </div>
    </form>

    @if (!request('kelas'))
    <div class="alert alert-warning text-center">
        Silakan pilih kelas terlebih dahulu untuk melihat jadwal.
    </div>
    @else
    {{-- Preview Jadwal Semua Kelas --}}
    @foreach ($dataCetak as $kelasData)
    <div class="mb-5">
        <div class="text-center">
            <h3 class="fw-bold mb-1">JADWAL PELAJARAN</h3>
            <h5 class="mb-2">Kelas {{ $kelasData['kelas'] }}</h5>
            <hr class="border border-dark border-1 opacity-100 w-50 mx-auto">
        </div>

        <div class="table-responsive">
            <table class="table table-bordered text-center align-middle shadow-sm">
                <thead class="table-light">
                    <tr>
                        <th class="jam-kolom">Jam</th>
                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                        <th>{{ $hari }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @for ($jam = 0; $jam <= 12; $jam++)
                        @if ($jam===4)
                        <tr class="table-secondary">
                        <td><strong>Istirahat 1<br><small>09:45 - 10:15</small></strong></td>
                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                        <td class="text-center text-muted">-</td>
                        @endforeach
                        </tr>
                        @elseif ($jam === 8)
                        <tr class="table-secondary">
                            <td><strong>Istirahat 2<br><small>12:15 - 12:50</small></strong></td>
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                            <td class="text-center text-muted">-</td>
                            @endforeach
                        </tr>
                        @else
                        <tr>
                            <td><strong>{{ $jamWaktu[$jam] ?? 'Jam ' . $jam }}</strong></td>
                            @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                            @php
                            $cell = $kelasData['jadwal'][$hari][$jam] ?? null;
                            @endphp
                            <td>
                                @if ($cell)
                                <div class="fw-semibold">{{ $cell['mapel'] ?? '' }}</div>
                                <div class="text-muted small">{{ $cell['guru'] ?? '' }}</div>
                                <div class="text-muted small">{{ $cell['ruangan'] ?? '' }}</div>
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endif
                        @endfor
                </tbody>




            </table>
        </div>
    </div>
    @endforeach
    @endif
</div>
@endsection