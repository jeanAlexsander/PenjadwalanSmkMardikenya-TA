<div class="modal fade" id="modalEditMapel" tabindex="-1" aria-labelledby="modalEditMapelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="formEditMapel" class="modal-content rounded-3 shadow-lg border-0">
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-id" name="id" />
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Mata Pelajaran</h5>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit-nama" class="form-label">Nama Mapel</label>
                        <input type="text" placeholder="Masukan Nama Mapel" class="form-control" id="edit-nama" name="nama_mata_pelajaran" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit-kode" class="form-label">Kode Mapel</label>
                        <input type="text" placeholder="Masukan Kode Mapel" class="form-control" id="edit-kode" name="kode_mata_pelajaran" required>
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
            </div>
        </form>
    </div>
</div>