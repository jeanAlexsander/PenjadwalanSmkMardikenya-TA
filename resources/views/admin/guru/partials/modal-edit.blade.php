{{-- Modal Edit Guru --}}
<div class="modal fade" id="modalEditGuru" tabindex="-1" aria-labelledby="modalEditGuruLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditGuru" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title">Edit Guru</h5>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="edit_id">

                <div class="mb-3">
                    <label for="edit_nama" class="form-label">Nama</label>
                    <input type="text"
                        class="form-control @error('name') is-invalid @enderror"
                        id="edit_nama" name="name"
                        placeholder="Masukkan nama guru" required>
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="edit_email" class="form-label">Email</label>
                    <input type="email"
                        class="form-control @error('email') is-invalid @enderror"
                        id="edit_email" name="email"
                        placeholder="Masukkan email guru" required>
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="edit_alamat" class="form-label">Alamat</label>
                    <textarea class="form-control @error('alamat') is-invalid @enderror"
                        id="edit_alamat" name="alamat" rows="3"
                        placeholder="Masukkan alamat guru"></textarea>
                    @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tombol Reset Password --}}
                <div class="mb-3">
                    <label class="form-label">Reset Password</label><br>
                    {{-- Tombol Reset Password (dalam modal edit) --}}
                    <button type="button"
                        class="btn btn-warning btn-open-reset-password btn-buka-modal-reset"
                        data-nama=""
                        data-url=""
                        data-bs-toggle="modal"
                        data-bs-target="#modalConfirmResetPassword">
                        <i class="fas fa-key me-1"></i> Reset Password ke Default
                    </button>

                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary border" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>