@if ($kelasTerpilih)
<!-- Modal Konfirmasi -->
<div class="modal fade" id="modalResetJadwal" tabindex="-1" aria-labelledby="modalResetLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        {{-- Form untuk mengirim permintaan reset --}}
        <form action="{{ url('/admin/penjadwalan/reset/' . $kelasTerpilih->id) }}" method="POST">
            @csrf
            {{-- @method('DELETE') tidak perlu jika pakai POST --}}
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="modalResetLabel">Konfirmasi Reset Jadwal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    Apakah Anda yakin ingin menghapus seluruh jadwal untuk
                    <strong>{{ $kelasTerpilih->nama_kelas }}</strong>?
                    Tindakan ini tidak dapat dibatalkan.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reset</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif