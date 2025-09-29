{{-- Modal Edit --}}
<div class="modal fade" id="modalEditRuangan" tabindex="-1" aria-labelledby="modalEditRuanganLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formEditRuangan" method="POST" class="modal-content rounded-3 shadow-lg border-0">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalEditRuanganLabel">Edit Ruangan</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="edit_nama" class="form-label">Nama Ruangan</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control rounded-2" placeholder="Masukkan nama ruangan" required>
                </div>
                <div class="mb-3">
                    <label for="edit_kapasitas" class="form-label">Kapasitas</label>
                    <input type="number" name="kapasitas" id="edit_kapasitas" class="form-control rounded-2" placeholder="Masukkan kapasitas" onkeydown="return event.key !== 'e' && event.key !== '-' && event.key !== '+' && event.key !== '.'" required>
                </div>
                <div class="mb-3">
                    <label for="edit_keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="edit_keterangan" class="form-control rounded-2" placeholder="Tulis keterangan ruangan" rows="3"></textarea>
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