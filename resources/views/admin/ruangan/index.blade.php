@extends('layouts.admin')

@section('title', 'Data Ruangan')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    {{-- Judul Halaman + Tombol --}}
    <div>
        <h1 class="mb-4">Data Ruangan</h1>

        <button class="btn btn-primary mb-4 w-auto"
            data-bs-toggle="modal"
            data-bs-target="#modalTambahRuangan">
            <i class="fas fa-plus me-1"></i> Tambah Ruangan
        </button>
    </div>

    <div class="flex-grow-1">
        <div class="table-responsive">
            {{-- Tabel Data --}}
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nama Ruangan</th>
                        <th>Kapasitas</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ruangan as $index => $r)
                    <tr class="text-center">
                        {{-- nomor lanjut per halaman --}}
                        <td>{{ ($ruangan->firstItem() ?? 0) + $index }}</td>
                        <td>{{ $r->nama }}</td>
                        <td>{{ $r->kapasitas }}</td>
                        <td style="white-space: normal;">{{ $r->keterangan ?? '-'}}</td>
                        <td>
                            {{-- Tombol Aksi --}}
                            <a href="#"
                                class="btn btn-sm btn-warning btn-edit"
                                data-bs-toggle="modal" data-bs-target="#modalEditRuangan"
                                data-id="{{ $r->id }}"
                                data-nama="{{ $r->nama }}"
                                data-kapasitas="{{ $r->kapasitas }}"
                                data-keterangan="{{ $r->keterangan }}">
                                <i class="fas fa-edit me-1"></i> Edit
                            </a>
                            <button type="button"
                                class="btn btn-sm btn-danger btn-delete"
                                data-bs-toggle="modal" data-bs-target="#modalHapusRuangan"
                                data-id="{{ $r->id }}"
                                data-nama="{{ $r->nama }}">
                                <i class="fas fa-trash-alt me-1"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center table-warning">Belum ada data ruangan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah shell (bukan fixed) --}}
    @if ($ruangan->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $ruangan->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection

@push('modals')
@include('admin.ruangan.partials.modal-tambah')
@include('admin.ruangan.partials.modal-edit')
@include('admin.ruangan.partials.modal-hapus')
@endpush

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Edit
        const editButtons = document.querySelectorAll('.btn-edit');
        const formEdit = document.getElementById('formEditRuangan');
        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                document.getElementById('edit_nama').value = this.dataset.nama || '';
                document.getElementById('edit_kapasitas').value = this.dataset.kapasitas || '';
                document.getElementById('edit_keterangan').value = this.dataset.keterangan || '';
                formEdit.action = `/admin/ruangan/${id}`;
            });
        });

        // Hapus
        const deleteButtons = document.querySelectorAll('.btn-delete');
        const formHapus = document.getElementById('formHapusRuangan');
        const namaHapus = document.getElementById('namaRuanganHapus');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama || '';
                if (namaHapus) namaHapus.textContent = nama;
                if (formHapus) formHapus.action = `/admin/ruangan/${id}`;
            });
        });
    });
</script>
@endpush