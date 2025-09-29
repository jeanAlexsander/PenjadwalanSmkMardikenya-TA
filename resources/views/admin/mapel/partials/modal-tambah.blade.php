{{-- Modal Tambah Mapel --}}
<div class="modal fade" id="modalTambahMapel" tabindex="-1" aria-labelledby="modalTambahMapelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <form action="{{ route('admin.mapel.store') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Mata Pelajaran</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="nama_mata_pelajaran" class="form-label">Nama Mapel</label>
                    <input type="text" placeholder="Masukan Nama Mapel" class="form-control" id="nama_mata_pelajaran" name="nama_mata_pelajaran" required>
                </div>
                <div class="mb-3">
                    <label for="kode_mata_pelajaran" class="form-label">Kode Mapel</label>
                    <input type="text" placeholder="Masukan Kode Mapel" class="form-control" id="kode_mata_pelajaran" name="kode_mata_pelajaran" required>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary border" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>