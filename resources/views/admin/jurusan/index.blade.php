@extends('layouts.admin')

@section('title', 'Data Jurusan Sekolah')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    {{-- Judul + tombol --}}
    <div>
        <h1 class="mb-4">Data Jurusan Sekolah</h1>
        <button class="btn btn-primary mb-4 w-auto"
            data-bs-toggle="modal"
            data-bs-target="#modalTambahJurusan">
            <i class="fas fa-plus me-1"></i> Tambah Jurusan
        </button>
    </div>

    {{-- Tabel Data Jurusan (flex-grow-1 mendorong pagination ke bawah) --}}
    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama Jurusan</th>
                        <th class="text-center">Keterangan</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataJurusan as $index => $jurusan)
                    <tr>
                        {{-- nomor lanjut per halaman --}}
                        <td class="text-center">{{ ($dataJurusan->firstItem() ?? 0) + $index }}</td>
                        <td class="text-center">{{ $jurusan->nama_jurusan }}</td>
                        <td class="text-center" style="white-space: normal;">
                            {{ $jurusan->keterangan }}
                        </td>
                        <td class="text-center">
                            <a href="#"
                                class="btn btn-sm btn-warning btn-edit"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditJurusan"
                                data-id="{{ $jurusan->id }}"
                                data-nama="{{ $jurusan->nama_jurusan }}"
                                data-keterangan="{{ $jurusan->keterangan }}">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <button type="button"
                                class="btn btn-sm btn-danger btn-delete"
                                data-bs-toggle="modal"
                                data-bs-target="#modalHapusJurusan"
                                data-id="{{ $jurusan->id }}"
                                data-nama="{{ $jurusan->nama_jurusan }}">
                                <i class="fas fa-trash-alt me-1"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center table-warning">
                            Belum ada data jurusan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah shell (bukan fixed) --}}
    @if ($dataJurusan->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $dataJurusan->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection

@push('modals')
@include('admin.jurusan.partials.modal-tambah')
@include('admin.jurusan.partials.modal-edit')
@include('admin.jurusan.partials.modal-hapus')
@endpush

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tombol Edit
        document.querySelectorAll('.btn-edit').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama; // data-nama
                const ket = this.dataset.keterangan;

                document.getElementById('edit_nama_jurusan').value = nama;
                document.getElementById('edit_keterangan').value = ket;
                document.getElementById('formEditJurusan').action = `/admin/jurusan/${id}`;
            });
        });

        // Tombol Hapus
        document.querySelectorAll('.btn-delete').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama;

                document.getElementById('namaJurusanHapus').textContent = nama;
                document.getElementById('formHapusJurusan').action = `/admin/jurusan/${id}`;
            });
        });
    });
</script>
@endpush