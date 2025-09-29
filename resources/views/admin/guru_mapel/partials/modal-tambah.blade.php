<!-- Modal Tambah Guru Mapel -->
<div class="modal fade" id="modalTambahGuruMapel" tabindex="-1" aria-labelledby="modalTambahGuruMapelLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="{{ route('admin.guru-mapel.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahGuruMapelLabel">Tambah Guru Mapel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                {{-- Dropdown Guru --}}
                <div class="mb-3">
                    <label for="guru_id" class="form-label">Guru</label>
                    <select name="guru_id" id="guru_id" class="form-select" required>
                        <option value="">-- Pilih Guru --</option>
                        @foreach ($guruList as $guru)
                        <option value="{{ $guru->id }}" {{ old('guru_id') == $guru->id ? 'selected' : '' }}>{{ $guru->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Dropdown Mata Pelajaran --}}
                <div class="mb-3">
                    <label for="mapel_id" class="form-label">Mata Pelajaran</label>
                    <select name="mapel_id" id="mapel_id" class="form-select" required>
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach ($mapelList as $mapel)
                        <option value="{{ $mapel->id }}" {{ old('mapel_id') == $mapel->id ? 'selected' : '' }}>{{ $mapel->nama_mata_pelajaran }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Dropdown Jenis Mapel --}}
                <div class="mb-3">
                    <label for="jenis" class="form-label">Jenis Mapel</label>
                    <select name="jenis" id="jenis" class="form-select" required>
                        <option value="">-- Pilih Jenis --</option>
                        <option value="TEORI" {{ old('jenis') == 'TEORI' ? 'selected' : '' }}>Teori</option>
                        <option value="PRAKTIKUM" {{ old('jenis') == 'PRAKTIKUM' ? 'selected' : '' }}>Praktikum</option>
                    </select>
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