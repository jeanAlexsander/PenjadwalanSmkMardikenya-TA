{{-- Modal Tambah Ruangan --}}
<div class="modal fade" id="modalTambahRuangan" tabindex="-1" aria-labelledby="modalTambahRuanganLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.ruangan.store') }}" method="POST" class="modal-content rounded-3 shadow-lg border-0">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Ruangan</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="nama_ruangan" class="form-label">Nama Ruangan</label>
                    <input type="text" name="nama" id="nama_ruangan" class="form-control rounded-2" placeholder="Masukkan nama ruangan" required>
                </div>
                <div class="mb-3">
                    <label for="kapasitas" class="form-label">Kapasitas</label>
                    <input type="number" name="kapasitas" id="kapasitas" class="form-control" onkeydown="return event.key !== 'e' && event.key !== '-' && event.key !== '+' && event.key !== '.'" placeholder="Masukan kapasitas" required>
                </div>
                <div class="mb-3">
                    <label for="keterangan" class="form-label">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" class="form-control rounded-2" placeholder="Tulis keterangan ruangan" rows="3"></textarea>
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