{{-- Modal Hapus Jadwal Khusus --}}
@foreach ($jadwal as $item)
<div class="modal fade" id="modalHapusKegiatan{{ $item->id }}" tabindex="-1" aria-labelledby="modalHapusKegiatanLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="{{ route('admin.jadwal_khusus.destroy', $item->id) }}" method="POST">
            @csrf
            @method('DELETE')

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalHapusKegiatanLabel{{ $item->id }}">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Kegiatan
                </h5>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    Apakah Anda yakin ingin menghapus kegiatan <strong>{{ $item->nama_kegiatan ?? '-' }}</strong>?
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
@endforeach