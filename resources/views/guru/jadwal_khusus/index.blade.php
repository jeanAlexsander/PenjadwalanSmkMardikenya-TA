@extends('layouts.main')

@section('content')
<h1 class="mb-4">Jadwal Khusus</h1>

<div class="table-responsive">
    <table class="table table-bordered text-center">
        <thead class="table-light">
            <tr>
                <th>No</th>
                <th>Kegiatan</th>
                <th>Tanggal</th>
                <th>Jam</th>
                <th>Kelas</th>
                <th>Ruang</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($jadwal as $i => $item)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $item->nama_kegiatan }}</td>
                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d-m-Y') }}</td>
                <td>
                    {{ $item->jam_mulai ? \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') : '-' }} -
                    {{ $item->jam_selesai ? \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') : '-' }}
                </td>
                <td>
                    @if ($item->untuk_semua_kelas)
                    <span class="badge bg-success">Semua Kelas</span>
                    @else
                    {{ $item->kelas->pluck('nama_kelas')->join(', ') }}
                    @endif
                </td>
                <td>{{ $item->ruangan->nama ?? '-' }}</td>
                <td class="text-wrap" style="white-space: normal;">
                    {{ $item->keterangan ?? '-' }}
                </td>
            </tr>
            @empty
            <td colspan="8" class="text-center table-warning">
                Belum ada jadwal khusus.
            </td>
            @endforelse
        </tbody>
    </table>
</div>
@endsection