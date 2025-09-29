@extends('layouts.admin')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    {{-- Judul + tombol --}}
    <div>
        <h1 class="mb-4">Jadwal Khusus</h1>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="d-flex justify-content-start mb-4">
            <button class="btn btn-primary w-auto" data-bs-toggle="modal" data-bs-target="#modalTambahKegiatan">
                <i class="fas fa-plus me-1"></i> Tambah Jadwal Khusus
            </button>
        </div>
    </div>

    {{-- Area tabel mendorong pagination ke bawah --}}
    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered table-hover text-nowrap text-center align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kegiatan</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th>Kelas</th>
                        <th>Ruang</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jadwal as $i => $item)
                    <tr>
                        {{-- nomor lanjut per halaman --}}
                        <td>{{ ($jadwal->firstItem() ?? 0) + $i }}</td>

                        <td>{{ $item->nama_kegiatan }}</td>

                        {{-- Tanggal (TZ Jakarta jika field bertipe datetime) --}}
                        <td>
                            {{ \Carbon\Carbon::parse($item->tanggal)->setTimezone('Asia/Jakarta')->format('d-m-Y') }}
                        </td>

                        <td>
                            {{ $item->jam_mulai ? \Carbon\Carbon::parse($item->jam_mulai)->setTimezone('Asia/Jakarta')->format('H:i') : '-' }}
                            –
                            {{ $item->jam_selesai ? \Carbon\Carbon::parse($item->jam_selesai)->setTimezone('Asia/Jakarta')->format('H:i') : '-' }}
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

                        <td>
                            <div class="d-inline-flex flex-wrap gap-2 justify-content-center">
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditKegiatan{{ $item->id }}">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapusKegiatan{{ $item->id }}">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center table-warning">Belum ada jadwal khusus.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah shell (bukan fixed) --}}
    @if ($jadwal->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $jadwal->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection

@push('modals')
@include('admin.jadwal_khusus.partials.modal-tambah')
@include('admin.jadwal_khusus.partials.modal-edit')
@include('admin.jadwal_khusus.partials.modal-hapus')
@endpush

@push('scripts')
{{-- script toggle "semua kelas" kamu tetap sama --}}
@endpush