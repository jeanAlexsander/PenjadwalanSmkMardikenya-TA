@extends('layouts.admin')

@section('title', 'Data Kelas')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    {{-- Judul Halaman + Tombol --}}
    <div>
        <h1 class="mb-4">Data Kelas</h1>
        <button class="btn btn-primary mb-4 w-auto" data-bs-toggle="modal" data-bs-target="#modalTambahKelas">
            <i class="fas fa-plus me-1"></i> Tambah Kelas
        </button>
    </div>

    {{-- Area tabel mendorong pagination ke bawah --}}
    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Jurusan</th>
                        <th>Tingkat</th>
                        <th>Wali Kelas</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kelases as $index => $kls)
                    <tr class="text-center">
                        {{-- nomor lanjut per halaman --}}
                        <td>{{ ($kelases->firstItem() ?? 0) + $index }}</td>

                        <td>{{ $kls->nama_kelas }}</td>
                        <td>{{ $kls->jurusan->nama_jurusan ?? '-' }}</td>
                        <td>{{ $kls->tingkat }}</td>
                        <td>{{ $kls->waliKelas->user->name ?? $kls->waliKelas->name ?? '-' }}</td>
                        <td>
                            <div class="d-inline-flex flex-wrap gap-2 justify-content-center">
                                <a href="#"
                                    class="btn btn-sm btn-warning btn-edit"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditKelas"
                                    data-id="{{ $kls->id }}"
                                    data-nama="{{ $kls->nama_kelas }}"
                                    data-jurusan="{{ $kls->jurusan_id }}"
                                    data-tingkat="{{ $kls->tingkat }}"
                                    data-wali="{{ $kls->wali_kelas_id ?? '' }}">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </a>
                                <button type="button"
                                    class="btn btn-sm btn-danger btn-delete"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalHapusKelas"
                                    data-id="{{ $kls->id }}"
                                    data-nama="{{ $kls->nama_kelas }}">
                                    <i class="fas fa-trash-alt me-1"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center table-warning">Belum ada data kelas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah shell (bukan fixed) --}}
    @if ($kelases->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $kelases->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection

@push('modals')
@include('admin.kelas.partials.modal-tambah')
@include('admin.kelas.partials.modal-edit')
@include('admin.kelas.partials.modal-hapus')
@endpush

<div id="gurus-options-el" data-gurus='@json($gurusOptionsPerKelas ?? [])'></div>

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Edit
        const editButtons = document.querySelectorAll('.btn-edit');
        const formEdit = document.getElementById('formEditKelas');
        const el = document.getElementById('gurus-options-el');
        window.gurusOptionsPerKelas = JSON.parse(el?.dataset?.gurus || '[]');

        function labelGuru(g) {
            if (g.user && g.user.name) return g.user.name;
            if (g.name) return g.name;
            return `Guru #${g.id}`;
        }

        editButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama;
                const jurusan = this.dataset.jurusan;
                const tingkat = this.dataset.tingkat;
                const waliId = this.dataset.wali || '';

                document.getElementById('edit_nama_kelas').value = nama;
                document.getElementById('edit_jurusan_id').value = jurusan;
                document.getElementById('edit_tingkat').value = tingkat;

                const waliSelect = document.getElementById('edit_wali_kelas_id');
                const allowed = (window.gurusOptionsPerKelas && window.gurusOptionsPerKelas[id]) ? window.gurusOptionsPerKelas[id] : [];

                waliSelect.innerHTML = '';
                const optEmpty = document.createElement('option');
                optEmpty.value = '';
                optEmpty.textContent = '-- Pilih Wali Kelas --';
                waliSelect.appendChild(optEmpty);

                let hasSelectedInAllowed = false;
                allowed.forEach(g => {
                    const o = document.createElement('option');
                    o.value = g.id;
                    o.textContent = labelGuru(g);
                    if (String(waliId) !== '' && String(g.id) === String(waliId)) {
                        o.selected = true;
                        hasSelectedInAllowed = true;
                    }
                    waliSelect.appendChild(o);
                });

                if (String(waliId) !== '' && !hasSelectedInAllowed) {
                    const o = document.createElement('option');
                    o.value = waliId;
                    o.selected = true;
                    o.textContent = `Wali saat ini (ID: ${waliId})`;
                    waliSelect.appendChild(o);
                }

                formEdit.action = `/admin/kelas/${id}`;
            });
        });

        // Hapus
        const deleteButtons = document.querySelectorAll('.btn-delete');
        const formHapus = document.getElementById('formHapusKelas');
        const namaHapus = document.getElementById('namaKelasHapus');
        deleteButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama;
                if (namaHapus) namaHapus.textContent = nama;
                if (formHapus) formHapus.action = `/admin/kelas/${id}`;
            });
        });
    });
</script>
@endpush