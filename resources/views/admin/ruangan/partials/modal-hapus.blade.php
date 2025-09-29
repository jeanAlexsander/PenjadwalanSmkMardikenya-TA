{{-- Modal Hapus Ruangan --}}
<div class="modal fade" id="modalHapusRuangan" tabindex="-1" aria-labelledby="modalHapusRuanganLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formHapusRuangan" method="POST" class="modal-content rounded-3 shadow-lg border-0">
            @csrf
            @method('DELETE')

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-semibold" id="modalHapusRuanganLabel">
                    <i class="fas fa-warning me-2"></i>Konfirmasi Hapus Ruangan
                </h5>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    Apakah Anda yakin ingin menghapus ruangan <strong id="namaRuanganHapus"></strong>?
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