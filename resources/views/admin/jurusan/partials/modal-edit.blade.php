{{-- Modal Edit Jurusan --}}
<div class="modal fade" id="modalEditJurusan" tabindex="-1" aria-labelledby="modalEditJurusanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditJurusan" method="POST" class="modal-content rounded-3 shadow-lg border-0">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalEditJurusanLabel">Edit Jurusan</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_nama_jurusan" class="form-label">Nama Jurusan</label>
                    <input type="text" name="nama_jurusan" id="edit_nama_jurusan" class="form-control" placeholder="Masukkan nama jurusan" required>
                </div>
                <div class="mb-3">
                    <label for="edit_keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="edit_keterangan" class="form-control" placeholder="Tulis keterangan jurusan" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>