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
                            <button
                                class="btn btn-sm btn-warning btn-edit-guru"
                                data-id="{{ $item->id }}"
                                data-nama="{{ $item->user->name ?? $item->name ?? '' }}"
                                data-email="{{ $item->user->email ?? $item->email ?? '' }}"
                                data-alamat="{{ $item->alamat ?? '' }}"
                                data-role="{{ $item->user->role ?? 'guru' }}" {{-- 'guru' atau 'kepala_sekolah' --}}
                                data-update-url="{{ route('admin.guru.update', $item->id) }}"
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
        // ========= ELEMENT REFERENCES =========
        const editButtons = document.querySelectorAll('.btn-edit-guru');
        const formEdit = document.getElementById('formEditGuru');
        const resetPasswordBtn = document.querySelector('#modalEditGuru .btn-open-reset-password');

        const selectRole = document.getElementById('edit_role'); // <select name="role" id="edit_role">
        const optKepsek = document.getElementById('opt_kepala_sekolah'); // <option id="opt_kepala_sekolah" value="kepala_sekolah">
        const kepsekExistsFlag = document.getElementById('kepsek_exists_flag'); // <input type="hidden" id="kepsek_exists_flag" value="1|0">
        const kepsekExists = (kepsekExistsFlag?.value === '1');

        // ========= SCRIPT RESET (dari kamu) =========
        document.querySelectorAll('.btn-buka-modal-reset').forEach(button => {
            button.addEventListener('click', function() {
                const url = this.dataset.url;
                const nama = this.dataset.nama;
                document.getElementById('resetForm').setAttribute('action', url);
                document.getElementById('namaGuruReset').textContent = nama;
            });
        });

        // ========= SCRIPT EDIT (disesuaikan: role + URL dinamis) =========
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.dataset.id;
                const nama = this.dataset.nama || '';
                const email = this.dataset.email || '';
                const alamat = this.dataset.alamat || '';
                const role = (this.dataset.role || 'guru'); // 'guru' | 'kepala_sekolah'
                const updateUrl = this.dataset.updateUrl || `/admin/guru/${id}`;
                const resetUrl = this.dataset.resetUrl || `/admin/guru/${id}/reset-password`;

                // Isi form edit guru
                document.getElementById('edit_id').value = id;
                document.getElementById('edit_nama').value = nama;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_alamat').value = alamat;

                // Set action form PUT ke route update
                formEdit.setAttribute('action', updateUrl);

                // Set tombol reset password di modal edit
                if (resetPasswordBtn) {
                    resetPasswordBtn.setAttribute('data-url', resetUrl);
                    resetPasswordBtn.setAttribute('data-nama', nama);
                }

                // === KUNCI: set dropdown ROLE sesuai data & UX guard ===
                // Jika sudah ada kepala sekolah & yang diedit BUKAN kepala sekolah → disable opsi kepsek (UX only)
                if (optKepsek) {
                    if (kepsekExists && role !== 'kepala_sekolah') {
                        optKepsek.setAttribute('disabled', 'disabled');
                        optKepsek.textContent = 'Kepala Sekolah (sudah ada)';
                    } else {
                        optKepsek.removeAttribute('disabled');
                        optKepsek.textContent = 'Kepala Sekolah';
                    }
                }

                // Set value select
                if (selectRole) {
                    selectRole.value = role;
                    // Fallback kalau value belum ke-set (mis. mismatch string)
                    if (selectRole.value !== role) {
                        [...selectRole.options].forEach(o => o.selected = (o.value === role));
                    }
                }

                // (Opsional) update judul modal
                const modalTitle = document.querySelector('#modalEditGuru .modal-title');
                if (modalTitle) modalTitle.textContent = 'Edit Guru: ' + nama;
            });
        });

        // ========= SCRIPT DELETE (punyamu) =========
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

        // ========= RESET PASSWORD via fetch (punyamu) =========
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

            // Saat validasi gagal, pastikan role juga ikut ter-set kembali
            if (selectRole) {
                const roleFromOld = window.editGuruData.role || 'guru';
                // UX guard ulang ketika re-open via error
                if (optKepsek) {
                    if (kepsekExists && roleFromOld !== 'kepala_sekolah') {
                        optKepsek.setAttribute('disabled', 'disabled');
                        optKepsek.textContent = 'Kepala Sekolah (sudah ada)';
                    } else {
                        optKepsek.removeAttribute('disabled');
                        optKepsek.textContent = 'Kepala Sekolah';
                    }
                }
                selectRole.value = roleFromOld;
                if (selectRole.value !== roleFromOld) {
                    [...selectRole.options].forEach(o => o.selected = (o.value === roleFromOld));
                }
            }
        }
    });
</script>
@endpush