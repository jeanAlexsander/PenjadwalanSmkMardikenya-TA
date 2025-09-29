{{-- Modal Hapus Guru --}}
<div class="modal fade" id="modalHapusGuru" tabindex="-1" aria-labelledby="modalHapusGuruLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="formHapusGuru" method="POST" class="modal-content rounded-3 shadow-lg border-0">
            @csrf
            @method('DELETE')

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalHapusGuruLabel">
                    <i class="fas fa-warning me-2"></i>Konfirmasi Hapus Guru
                </h5>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    Apakah Anda yakin ingin menghapus guru <strong id="namaGuruHapus"></strong>?
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