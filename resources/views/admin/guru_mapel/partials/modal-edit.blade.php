{{-- Modal Edit Guru Mapel --}}
@foreach ($guruMapel as $item)
<div class="modal fade" id="modalEditGuruMapel{{ $item->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.guru-mapel.update', $item->id) }}" method="POST" class="modal-content">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditGuruMapelLabel{{ $item->id }}">
                    Edit Guru Mapel
                </h5>
            </div>

            <div class="modal-body">
                {{-- Dropdown Guru --}}
                <div class="mb-3">
                    <label for="guru_id_{{ $item->id }}" class="form-label">Guru</label>
                    <select name="guru_id" id="guru_id_{{ $item->id }}" class="form-select" required>
                        <option value="" disabled>Pilih Guru</option>
                        @foreach ($guruList as $guru)
                        <option value="{{ $guru->id }}"
                            {{ old('guru_id', $item->guru_id) == $guru->id ? 'selected' : '' }}>
                            {{ $guru->name ?? ($guru->user->name ?? '—') }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Dropdown Mapel --}}
                <div class="mb-3">
                    <label for="mapel_id_{{ $item->id }}" class="form-label">Mata Pelajaran</label>
                    <select name="mata_pelajaran_id" id="mapel_id_{{ $item->id }}" class="form-select" required>
                        <option value="" disabled>Pilih Mata Pelajaran</option>
                        @foreach ($mapelList as $mapel)
                        <option value="{{ $mapel->id }}"
                            {{ old('mata_pelajaran_id', $item->mata_pelajaran_id) == $mapel->id ? 'selected' : '' }}>
                            {{ $mapel->nama_mata_pelajaran }}
                        </option>
                        @endforeach
                    </select>
                </div>

                {{-- Jenis Mapel --}}
                <div class="mb-3">
                    <label for="jenis_mapel_{{ $item->id }}" class="form-label">Jenis Mapel</label>
                    <select name="jenis" id="jenis_mapel_{{ $item->id }}" class="form-select" required>
                        <option value="TEORI" {{ old('jenis', $item->jenis) == 'TEORI' ? 'selected' : '' }}>Teori</option>
                        <option value="PRAKTIKUM" {{ old('jenis', $item->jenis) == 'PRAKTIKUM' ? 'selected' : '' }}>Praktikum</option>
                    </select>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-1"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach