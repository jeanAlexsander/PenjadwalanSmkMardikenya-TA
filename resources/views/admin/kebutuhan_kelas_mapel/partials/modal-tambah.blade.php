<div class="modal fade" id="modalTambahKebutuhan" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.kebutuhan-mapel-kelas.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Kebutuhan Mapel Kelas</h5>
                </div>

                <div class="modal-body">
                    {{-- Kelas --}}
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        @if($kelasTerpilih)
                        <input type="hidden" name="kelas_id" value="{{ $kelasTerpilih->id }}">
                        <input type="text" class="form-control" value="{{ $kelasTerpilih->nama_kelas }}" disabled>
                        @else
                        <select name="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($listKelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>

                    {{-- Guru × Mapel --}}
                    <div class="mb-3">
                        <label class="form-label">Guru dan Mata Pelajaran</label>
                        <select name="guru_mata_pelajaran_id" class="form-select" required>
                            <option value="">Pilih Guru dan Mapel</option>
                            @foreach($guruMapel as $gm)
                            <option value="{{ $gm->id }}">
                                {{ $gm->mataPelajaran->nama_mata_pelajaran ?? $gm->mataPelajaran->nama ?? 'Mapel ?' }}
                                - {{ $gm->guru->name ?? $gm->guru->name ?? 'Guru ?' }}
                                ({{ strtoupper($gm->jenis) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Jumlah JP/Minggu --}}
                    <div class="mb-3">
                        <label class="form-label">Jumlah JP per Minggu</label>
                        <input
                            type="number"
                            name="jumlah_jam_per_minggu"
                            class="form-control"
                            min="1" max="40" step="1"
                            value="{{ old('jumlah_jam_per_minggu', 1) }}"
                            placeholder="Masukkan jumlah JP per minggu"
                            required>
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
</div>