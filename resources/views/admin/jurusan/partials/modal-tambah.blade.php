{{-- Modal Tambah Jurusan --}}
<div class="modal fade" id="modalTambahJurusan" tabindex="-1" aria-labelledby="modalTambahJurusanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.jurusan.store') }}" method="POST" class="modal-content rounded-3 shadow-lg border-0">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Jurusan Baru</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="nama_jurusan" class="form-label">Nama Jurusan</label>
                    <input type="text" name="nama_jurusan" id="nama_jurusan" class="form-control" placeholder="Masukkan nama jurusan" required>
                </div>
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control" placeholder="Tulis keterangan jurusan" rows="3" required></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>