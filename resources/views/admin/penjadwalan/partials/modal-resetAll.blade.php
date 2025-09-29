<!-- Modal Reset Semua -->
<div class="modal fade" id="modalResetSemua" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.penjadwalan.resetAll') }}" method="POST" class="modal-content">
            @csrf
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Konfirmasi Reset Jadwal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Apakah Anda yakin ingin menghapus <strong>seluruh jadwal</strong>?
                Aksi ini <strong>permanen</strong> dan tidak dapat dibatalkan.
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Reset</button>
            </div>
        </form>
    </div>
</div>