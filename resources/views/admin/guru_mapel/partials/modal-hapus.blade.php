{{-- Modal Hapus Guru Mapel --}}
@foreach ($guruMapel as $item)
<div class="modal fade" id="modalHapusGuruMapel{{ $item->id }}" tabindex="-1" aria-labelledby="modalHapusGuruMapelLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="{{ route('admin.guru-mapel.destroy', $item->id) }}" method="POST">
            @csrf
            @method('DELETE')

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalHapusGuruMapelLabel{{ $item->id }}">
                    <i class="fas fa-warning me-2"></i>Konfirmasi Hapus Guru Mapel
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    Apakah kamu yakin ingin menghapus data
                    <strong>{{ $item->guru->name }} - {{ $item->mataPelajaran->nama_mata_pelajaran }}</strong>
                    dari daftar?
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