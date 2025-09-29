{{-- Modal Hapus Kebutuhan Kelas Mapel --}}
@foreach($kebutuhan as $item)
<div class="modal fade" id="modalHapusKebutuhan{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('admin.kebutuhan-mapel-kelas.destroy', $item->id) }}">
            @csrf
            @method('DELETE')

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="fas fa-warning me-2"></i>Konfirmasi Hapus Kebutuhan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    Apakah Anda yakin ingin menghapus kebutuhan untuk:<br>
                    <strong>{{ $item->kelas->nama_kelas ?? '-' }}</strong> —
                    <strong>
                        {{ $item->guruMapel->mataPelajaran->nama_mata_pelajaran 
                            ?? $item->guruMapel->mataPelajaran->nama 
                            ?? '-' }}
                    </strong>
                    ({{ $item->guruMapel->guru->name ?? '-' }})?
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