@extends('layouts.KepalaSekolah')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    <h1 class="mb-4">Jadwal Khusus</h1>

    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered text-center mb-0">
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
                        {{-- nomor lanjut per halaman --}}
                        <td>{{ ($jadwal->firstItem() ?? 0) + $i }}</td>

                        <td>{{ $item->nama_kegiatan ?? '-' }}</td>

                        <td>
                            {{ $item->tanggal
                                ? \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Jakarta')->format('d-m-Y')
                                : '-' }}
                        </td>

                        <td>
                            {{ $item->jam_mulai
                                ? \Carbon\Carbon::parse($item->jam_mulai)->setTimezone('Asia/Jakarta')->format('H:i')
                                : '-' }}
                            –
                            {{ $item->jam_selesai
                                ? \Carbon\Carbon::parse($item->jam_selesai)->setTimezone('Asia/Jakarta')->format('H:i')
                                : '-' }}
                        </td>

                        <td>
                            @if (!empty($item->untuk_semua_kelas))
                            <span class="badge bg-success">Semua Kelas</span>
                            @else
                            {{ optional($item->kelas)->pluck('nama_kelas')->join(', ') ?? '-' }}
                            @endif
                        </td>

                        <td>{{ $item->ruangan->nama ?? '-' }}</td>

                        <td class="text-wrap" style="white-space: normal;">
                            {{ $item->keterangan ?? '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center table-warning">Belum ada jadwal khusus.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah container --}}
    @if ($jadwal->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $jadwal->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection