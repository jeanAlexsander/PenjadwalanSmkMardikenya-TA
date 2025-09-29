{{-- Modal Tambah --}}
<div class="modal fade" id="modalTambahKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.kelas.store') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kelas</h5>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Kelas</label>
                    <input type="text" placeholder="Masukan Nama Kelas" name="nama_kelas" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jurusan</label>
                    <select name="jurusan_id" class="form-select" required>
                        <option value="">-- Pilih Jurusan --</option>
                        @foreach ($jurusans as $j)
                        <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tingkat</label>
                    <select name="tingkat" class="form-select" required>
                        <option value="">-- Pilih Tingkat --</option>
                        @foreach ([10,11,12] as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Wali Kelas (opsional)</label>
                    <select name="wali_kelas_id" class="form-select">
                        <option value="">-- Pilih Wali Kelas --</option>
                        @foreach ($gurusAvailableForCreate as $g)
                        <option value="{{ $g->id }}">{{ $g->user->name ?? $g->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Hanya guru yang belum menjadi wali kelas.</div>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary border" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>