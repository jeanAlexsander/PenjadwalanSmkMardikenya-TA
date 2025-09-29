{{-- Modal Hapus Kelas --}}
<div class="modal fade" id="modalHapusKelas" tabindex="-1" aria-labelledby="modalHapusKelasLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formHapusKelas" method="POST" class="modal-content">
            @csrf
            @method('DELETE')

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalHapusKelasLabel">
                    <i class="fas fa-warning me-2"></i>Konfirmasi Hapus Kelas
                </h5>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    Apakah Anda yakin ingin menghapus kelas <strong id="namaKelasHapus"></strong>?
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