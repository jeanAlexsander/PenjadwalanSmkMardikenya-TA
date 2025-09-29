{{-- Modal Edit --}}
<div class="modal fade" id="modalEditKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="" id="formEditKelas" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title">Edit Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Kelas</label>
                    <input type="text" name="nama_kelas" id="edit_nama_kelas" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jurusan</label>
                    <select name="jurusan_id" id="edit_jurusan_id" class="form-select" required>
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach ($jurusans as $j)
                        <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tingkat</label>
                    <select name="tingkat" id="edit_tingkat" class="form-select" required>
                        @foreach ([10,11,12] as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Wali Kelas (opsional)</label>
                    <select name="wali_kelas_id" id="edit_wali_kelas_id" class="form-select">
                    </select>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary border" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>