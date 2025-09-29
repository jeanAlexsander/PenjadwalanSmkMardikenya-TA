<!-- Modal Konfirmasi Reset Password -->
<div class="modal fade" id="modalConfirmResetPassword" tabindex="-1" aria-labelledby="modalLabelReset" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form id="resetForm" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabelReset">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin mereset password <strong id="namaGuruReset">[Nama Guru]</strong> ke password default guru123?
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-warning">Ya, Reset</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                </div>
            </div>
        </form>
    </div>
</div>