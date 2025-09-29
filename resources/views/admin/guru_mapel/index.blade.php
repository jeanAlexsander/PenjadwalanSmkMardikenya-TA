@extends('layouts.admin')

@section('content')
<div class="container-fluid page-shell"> {{-- <- shell flex full height --}}
    {{-- Judul + tombol --}}
    <h1 class="mb-4">Data Guru Mapel</h1>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="d-flex justify-content-start mb-4"> {{-- perbaiki class justify-content --}}
        <button class="btn btn-primary w-auto" data-bs-toggle="modal" data-bs-target="#modalTambahGuruMapel">
            <i class="fas fa-plus me-1"></i> Tambah Guru Mapel
        </button>
    </div>

    {{-- AREA TABEL: mendorong pagination ke bawah --}}
    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered text-nowrap text-center align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Guru</th>
                        <th>Mata Pelajaran</th>
                        <th>Jenis</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guruMapel as $i => $item)
                    <tr>
                        {{-- nomor lanjut per halaman --}}
                        <td>{{ ($guruMapel->firstItem() ?? 0) + $i }}</td>

                        {{-- sesuaikan: kalau nama ada di user, pakai nullsafe --}}
                        <td>{{ $item->guru->name ?? $item->guru->user->name ?? '-' }}</td>
                        <td>{{ $item->mataPelajaran->nama_mata_pelajaran }}</td>
                        <td>{{ ucfirst($item->jenis) }}</td>
                        <td>
                            <div class="d-inline-flex flex-wrap gap-2 justify-content-center">
                                <button class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditGuruMapel{{ $item->id }}">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalHapusGuruMapel{{ $item->id }}">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center table-warning">Belum ada data guru mapel.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION: nempel di bawah shell (bukan fixed) --}}
    @if ($guruMapel->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $guruMapel->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection

@push('modals')
{{-- Modal Tambah --}}
@include('admin.guru_mapel.partials.modal-tambah')

{{-- Modal Edit & Hapus --}}
@include('admin.guru_mapel.partials.modal-edit')
@include('admin.guru_mapel.partials.modal-hapus')
@endpush