@extends('layouts.admin')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    {{-- Judul Halaman + Tombol --}}
    <div>
        <h1 class="mb-4">Data Guru</h1>

        <button class="btn btn-primary mb-4 w-auto" data-bs-toggle="modal" data-bs-target="#modalTambahGuru">
            <i class="fas fa-plus me-1"></i> Tambah Guru
        </button>
    </div>

    {{-- Tabel (flex-grow-1 mendorong pagination ke bawah) --}}
    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Email</th>
                        <th>Jenis Kelamin</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guru as $index => $item)
                    <tr>
                        {{-- nomor lanjut per halaman --}}
                        <td>{{ ($guru->firstItem() ?? 0) + $index }}</td>

                        {{-- prefer user->name/email, fallback ke kolom di tabel guru jika ada --}}
                        <td>{{ $item->user->name ?? $item->name ?? '-' }}</td>
                        <td>{{ $item->nip ?? '-' }}</td>
                        <td>{{ $item->user->email ?? $item->email ?? '-' }}</td>
                        <td>{{ $item->jenis_kelamin ?? '-' }}</td>
                        <td>{{ $item->alamat ?? '-' }}</td>

                        <td>
                            <button class="btn btn-sm btn-warning btn-edit-guru"
                                data-id="{{ $item->id }}"
                                data-nama="{{ $item->user->name ?? $item->name ?? '' }}"
                                data-email="{{ $item->user->email ?? $item->email ?? '' }}"
                                data-alamat="{{ $item->alamat ?? '' }}"
                                data-reset-url="{{ route('admin.guru.resetPassword', $item->id) }}"
                                data-bs-toggle="modal"
                                data-bs-target="#modalEditGuru">
                                <i class="fas fa-edit"></i> Edit
                            </button>

                            <button class="btn btn-sm btn-danger"
                                data-bs-toggle="modal"
                                data-bs-target="#modalHapusGuru"
                                data-id="{{ $item->id }}"
                                data-nama="{{ $item->user->name ?? $item->name ?? '' }}">
                                <i class="fas fa-trash-alt me-1"></i> Hapus
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center table-warning">Belum ada data guru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah shell (bukan fixed) --}}
    @if ($guru->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $guru->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection

@push('modals')
@include('admin.guru.partials.modal-tambah')
@include('admin.guru.partials.modal-edit')
@include('admin.guru.partials.modal-hapus')
@include('admin.guru.partials.modal-reset-password')
@endpush

{{-- Buka modal tambah jika validasi tambah gagal --}}
@if ($errors->any() && session('tampilModalTambahGuru'))
<script>
    window.tampilModalTambahGuru = true;
</script>
@endif

{{-- Buka modal edit jika validasi edit gagal --}}
@if ($errors->any() && session('tampilModalEditGuru'))
<script>
    window.tampilModalEditGuru = true;
    window.editGuruData = {
        id: "{{ session('editGuruId') }}",
        name: "{{ old('name') }}",
        email: "{{ old('email') }}",
        alamat: `{{ old('alamat') }}`
    };
</script>
@endif

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // ========= SCRIPT EDIT =========
        const editButtons = document.querySelectorAll('.btn-edit-guru');
        const formEdit = document.getElementById('formEditGuru');
        const resetPasswordBtn = document.querySelector('#modalEditGuru .btn-open-reset-password');

        document.querySelectorAll('.btn-buka-modal-reset').forEach(button => {
            button.addEventListener('click', function() {
                const url = this.dataset.url;
                const nama = this.dataset.nama;
                document.getElementById('resetForm').setAttribute('action', url);
                document.getElementById('namaGuruReset').textContent = nama;
            });
        });

        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama || '';
                const email = this.dataset.email || '';
                const alamat = this.dataset.alamat || '';

                // Isi form edit guru
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_nama').value = nama;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_alamat').value = alamat;

                // Set action form untuk PUT ke /admin/guru/{id}
                formEdit.setAttribute('action', `/admin/guru/${id}`);

                // Set tombol reset password (dalam modal edit)
                if (resetPasswordBtn) {
                    resetPasswordBtn.setAttribute('data-url', `/admin/guru/${id}/reset-password`);
                    resetPasswordBtn.setAttribute('data-nama', nama);
                }
            });
        });

        // ========= SCRIPT DELETE =========
        const deleteButtons = document.querySelectorAll('[data-bs-target="#modalHapusGuru"]');
        const formHapus = document.getElementById("formHapusGuru");
        const namaGuru = document.getElementById("namaGuruHapus");

        deleteButtons.forEach(button => {
            button.addEventListener("click", function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama;
                formHapus.action = `/admin/guru/${id}`;
                namaGuru.textContent = nama;
            });
        });

        // ========= RESET PASSWORD =========
        const confirmResetModal = document.getElementById('modalConfirmResetPassword');
        const spanNamaGuru = document.getElementById('namaGuruReset');
        const btnKonfirmasiReset = document.getElementById('btnKonfirmasiReset');
        let currentResetUrl = '';

        confirmResetModal?.addEventListener('show.bs.modal', function(event) {
            const triggerButton = event.relatedTarget;
            const nama = triggerButton?.getAttribute('data-nama');
            const url = triggerButton?.getAttribute('data-url');
            spanNamaGuru.textContent = nama || '';
            currentResetUrl = url || '';
        });

        btnKonfirmasiReset?.addEventListener('click', function(e) {
            e.preventDefault();
            if (!currentResetUrl) return;
            fetch(currentResetUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(r => {
                    if (!r.ok) throw new Error('Gagal mereset password');
                    return r.json();
                })
                .then(data => {
                    alert(data.message || 'Password berhasil direset');
                    const modal = bootstrap.Modal.getInstance(confirmResetModal);
                    modal?.hide();
                })
                .catch(err => {
                    console.error("Gagal reset password:", err);
                    alert("Terjadi kesalahan saat mereset password");
                });
        });

        // ========= TAMPILKAN MODAL SAAT VALIDASI GAGAL =========
        if (window.tampilModalTambahGuru) {
            new bootstrap.Modal(document.getElementById('modalTambahGuru')).show();
        }
        if (window.tampilModalEditGuru && window.editGuruData) {
            const modalEdit = new bootstrap.Modal(document.getElementById('modalEditGuru'));
            modalEdit.show();
            document.getElementById('edit_id').value = window.editGuruData.id;
            document.getElementById('edit_nama').value = window.editGuruData.name;
            document.getElementById('edit_email').value = window.editGuruData.email;
            document.getElementById('edit_alamat').value = window.editGuruData.alamat;
            document.getElementById('formEditGuru').action = `/admin/guru/${window.editGuruData.id}`;
        }
    });
</script>
@endpush