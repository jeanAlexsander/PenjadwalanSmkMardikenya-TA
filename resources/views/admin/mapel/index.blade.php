@extends('layouts.admin')

@section('content')
<div class="container-fluid page-shell"> {{-- <- flex kolom full height --}}
    <div>
        {{-- Judul Halaman --}}
        <h1 class='mb-4'>Data Mata Pelajaran</h1>

        <button class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#modalTambahMapel">
            <i class="fas fa-plus me-1"></i> Tambah Mata Pelajaran
        </button>
    </div>
    {{-- AREA TABEL: mendorong pagination ke bawah --}}
    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Mata Pelajaran</th>
                        <th>Kode</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mataPelajaran as $index => $item)
                    <tr>
                        <td>{{ ($mataPelajaran->firstItem() ?? 0) + $index }}</td>
                        <td>{{ $item->nama_mata_pelajaran }}</td>
                        <td>{{ $item->kode_mata_pelajaran }}</td>
                        <td>
                            <div class="d-inline-flex flex-wrap gap-2 justify-content-center">
                                <button type="button" class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal" data-bs-target="#modalEditMapel"
                                    data-id="{{ $item->id }}"
                                    data-nama="{{ $item->nama_mata_pelajaran }}"
                                    data-kode="{{ $item->kode_mata_pelajaran }}">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                <button type="button" class="btn btn-sm btn-danger"
                                    data-bs-toggle="modal" data-bs-target="#modalHapusMapel"
                                    data-id="{{ $item->id }}"
                                    data-nama="{{ $item->nama_mata_pelajaran }}">
                                    <i class="fas fa-trash-alt me-1"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center table-warning">Belum ada data mata pelajaran.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PAGINATION: nempel di bawah shell (bukan fixed) --}}
    @if ($mataPelajaran->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $mataPelajaran->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection


@push('modals')
@include('admin.mapel.partials.modal-tambah')
@include('admin.mapel.partials.modal-edit')
@include('admin.mapel.partials.modal-hapus')
@endpush

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // ===== EDIT MAPEL =====
        const inputId = document.getElementById('edit-id');
        const inputNama = document.getElementById('edit-nama');
        const inputKode = document.getElementById('edit-kode');
        const formEdit = document.getElementById('formEditMapel');

        // Tombol edit
        const editButtons = document.querySelectorAll('[data-bs-target="#modalEditMapel"]');

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama;
                const kode = this.dataset.kode;

                // Set value input di modal
                inputId.value = id;
                inputNama.value = nama;
                inputKode.value = kode;

                // Set action form edit
                formEdit.action = `/admin/mapel/${id}`;
            });
        });

        // ===== HAPUS MAPEL =====
        const formHapus = document.getElementById('formHapusMapel');
        const spanNamaHapus = document.getElementById('namaMapelHapus');

        // Tombol hapus
        const deleteButtons = document.querySelectorAll('[data-bs-target="#modalHapusMapel"]');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama;

                // Tampilkan nama mapel di modal konfirmasi
                spanNamaHapus.textContent = nama;

                // Set action form hapus
                formHapus.action = `/admin/mapel/${id}`;
            });
        });
    });
</script>
@endpush