{{-- Modal Hapus Jurusan --}}
<div class="modal fade" id="modalHapusJurusan" tabindex="-1" aria-labelledby="modalHapusJurusanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formHapusJurusan" method="POST" class="modal-content rounded-3 shadow-lg border-0">
            @csrf
            @method('DELETE')

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-semibold" id="modalHapusJurusanLabel">
                    <i class="fas fa-warning me-2"></i>Konfirmasi Hapus Jurusan
                </h5>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    Apakah Anda yakin ingin menghapus jurusan <strong id="namaJurusanHapus"></strong>?
                </p>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> Hapus
                </button>
            </div>
        </form>
    </div>
</div>