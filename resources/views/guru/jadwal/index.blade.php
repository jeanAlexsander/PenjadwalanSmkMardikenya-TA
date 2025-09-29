@extends('layouts.main')

@section('content')
<h1 class="mb-4">Jadwal Mengajar</h1>

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
            // Urutkan item per hari: pakai jam_sort jika ada, selain itu pakai jam string
            $items = $grouped[$hari]->sortBy(function($row){
            // array access
            if (is_array($row)) {
            return $row['jam_sort'] ?? $row['jam'] ?? 9999;
            }
            // object access (jaga-jaga)
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
            <td colspan="6" class="text-center table-warning">
                Belum ada jadwal mengajar.
            </td>
            @endif
        </tbody>
    </table>
</div>
@endsection