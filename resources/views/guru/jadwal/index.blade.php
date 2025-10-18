@extends('layouts.main')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="mb-0">Jadwal Mengajar</h1>
    <div class="no-print">
        <button onclick="window.print()" class="btn btn-outline-primary">
            <i class="fas fa-print me-1"></i> Cetak
        </button>
        <a href="{{ route('guru.jadwal.cetak.pdf') }}" class="btn btn-primary">
            <i class="fas fa-file-pdf me-1"></i> Unduh PDF
        </a>
    </div>
</div>

<style>
    /* elemen yang tidak perlu ikut tercetak */
    @media print {

        .no-print,
        .no-print * {
            display: none !important;
        }

        /* rapikan tabel saat print */
        .table {
            border-collapse: collapse !important;
        }

        .table th,
        .table td {
            border: 1px solid #000 !important;
            padding: 6px 8px !important;
        }

        /* hilangkan warna bootstrap agar hemat tinta */
        .table-primary,
        .table-success,
        .table-warning,
        .table-info,
        .table-light {
            background: #fff !important;
        }

        /* header halaman */
        @page {
            size: A4 portrait;
            margin: 12mm;
        }

        h1 {
            font-size: 18pt;
            margin-bottom: 12px;
        }
    }
</style>

<div class="table-responsive">
    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>Hari</th>
                <th>Jam</th>
                <th>Mata Pelajaran</th>
                <th>Kelas</th>
                <th>Ruangan</th>
            </tr>
        </thead>
        <tbody>
            @php
            $grouped = $jadwal->groupBy('hari');
            $hariUrut = ['Senin','Selasa','Rabu','Kamis','Jumat'];
            $warnaHari = [
            'Senin' => 'table-primary',
            'Selasa'=> 'table-success',
            'Rabu' => 'table-warning',
            'Kamis' => 'table-info',
            'Jumat' => 'table-light',
            ];
            $printed = false;
            @endphp

            @foreach ($hariUrut as $hari)
            @if ($grouped->has($hari))
            @php
            $items = $grouped[$hari]->sortBy(function($row){
            if (is_array($row)) return $row['jam_sort'] ?? $row['jam'] ?? 9999;
            return $row->jam_sort ?? $row->jam ?? 9999;
            })->values();
            @endphp

            @foreach ($items as $idx => $item)
            <tr class="{{ $warnaHari[$hari] ?? 'table-light' }}">
                @if ($idx === 0)
                <td rowspan="{{ $items->count() }}" class="align-middle text-center fw-bold">{{ $hari }}</td>
                @endif
                <td>{{ is_array($item) ? ($item['jam'] ?? '-') : ($item->jam ?? '-') }}</td>
                <td>{{ is_array($item) ? ($item['mapel'] ?? '-') : ($item->mapel ?? '-') }}</td>
                <td>{{ is_array($item) ? ($item['kelas'] ?? '-') : ($item->kelas ?? '-') }}</td>
                <td>{{ is_array($item) ? ($item['ruang'] ?? '-') : ($item->ruang ?? '-') }}</td>
            </tr>
            @endforeach

            @php $printed = true; @endphp
            @endif
            @endforeach

            @if (!$printed)
            <td colspan="6" class="text-center table-warning">Belum ada jadwal mengajar.</td>
            @endif
        </tbody>
    </table>
</div>
@endsection