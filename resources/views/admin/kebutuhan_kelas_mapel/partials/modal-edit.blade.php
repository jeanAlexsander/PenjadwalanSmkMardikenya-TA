@foreach($kebutuhan as $item)
<div class="modal fade" id="modalEditKebutuhan{{ $item->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.kebutuhan-mapel-kelas.update', $item->id) }}">
                @csrf
                @method('PUT')

                <div class="modal-header">
                    <h5 class="modal-title">Edit Kebutuhan — {{ $item->kelas->nama_kelas ?? '-' }}</h5>
                </div>

                <div class="modal-body">
                    {{-- Kelas --}}
                    <div class="mb-3">
                        <label class="form-label">Kelas</label>
                        <select name="kelas_id" class="form-select" required>
                            @foreach($listKelas as $k)
                            <option value="{{ $k->id }}" {{ $item->kelas_id == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Guru × Mapel --}}
                    <div class="mb-3">
                        <label class="form-label">Guru × Mata Pelajaran</label>
                        <select name="guru_mata_pelajaran_id" class="form-select" required>
                            @foreach($guruMapel as $gm)
                            <option value="{{ $gm->id }}" {{ $item->guru_mata_pelajaran_id == $gm->id ? 'selected' : '' }}>
                                {{ $gm->mataPelajaran->nama_mata_pelajaran ?? $gm->mataPelajaran->nama ?? 'Mapel ?' }}
                                — {{ $gm->guru->user->name ?? $gm->guru->name ?? 'Guru ?' }}
                                ({{ strtoupper($gm->jenis) }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- JP per minggu --}}
                    <div class="mb-3">
                        <label class="form-label">Jumlah JP per Minggu</label>
                        <input type="number" min="1" max="40" name="jumlah_jam_per_minggu" class="form-control"
                            value="{{ $item->jumlah_jam_per_minggu }}" required>
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
</div>
@endforeach