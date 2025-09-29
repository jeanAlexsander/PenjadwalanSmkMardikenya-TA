{{-- Modal Ganti Password --}}
<div class="modal fade" id="modalGantiPassword" tabindex="-1" aria-labelledby="modalGantiPasswordLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.profil.update-password') }}" method="POST" class="modal-content rounded-3 shadow-lg border-0">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title" id="modalGantiPasswordLabel">Ganti Password</h5>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label>Password Lama</label>
                    <input type="password" name="old_password" class="form-control" required>
                    @error('old_password')
                    <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_baru" class="form-label">Password Baru</label>
                    <input type="password" name="new_password" id="new_password" class="form-control" required>
                    @error('password_baru')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password_baru_confirmation" class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" required>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>