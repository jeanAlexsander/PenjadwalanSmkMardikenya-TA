{{-- Modal Edit Jadwal Khusus --}}
@foreach ($jadwal as $item)
<div class="modal fade" id="modalEditKegiatan{{ $item->id }}" tabindex="-1" aria-labelledby="modalEditKegiatanLabel{{ $item->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="{{ route('admin.jadwal_khusus.update', $item->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditKegiatanLabel{{ $item->id }}">Edit Jadwal Khusus</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nama Kegiatan</label>
                    <input type="text" name="nama_kegiatan" class="form-control" value="{{ $item->nama_kegiatan }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ \Carbon\Carbon::parse($item->tanggal)->format('Y-m-d') }}" required>

                </div>

                <div class="mb-3">
                    <label class="form-label">Jam Mulai</label>
                    <input type="time" name="jam_mulai" class="form-control" value="{{ $item->jam_mulai ? \Carbon\Carbon::parse($item->jam_mulai)->format('H:i') : '' }}">
                </div>

                <div class="mb-3">
                    <label class="form-label">Jam Selesai</label>
                    <input type="time" name="jam_selesai" class="form-control" value="{{ $item->jam_selesai ? \Carbon\Carbon::parse($item->jam_selesai)->format('H:i') : '' }}">
                </div>

                {{-- EDIT --}}
                <div class="form-check mb-3">
                    <input type="checkbox"
                        name="untuk_semua_kelas"
                        id="untuk_semua_kelas_{{ $item->id }}"
                        class="form-check-input js-semua-kelas"
                        data-target="#kelas-container-{{ $item->id }}"
                        {{ $item->untuk_semua_kelas ? 'checked' : '' }}>
                    <label class="form-check-label" for="untuk_semua_kelas_{{ $item->id }}">
                        Untuk Semua Kelas
                    </label>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <div class="row" id="kelas-container-{{ $item->id }}">
                        @foreach ($kelasList as $kelas)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check">
                                <input type="checkbox"
                                    name="kelas_id[]"
                                    value="{{ $kelas->id }}"
                                    id="kelas_edit_{{ $item->id }}_{{ $kelas->id }}"
                                    class="form-check-input"
                                    {{ $item->kelas->contains($kelas->id) ? 'checked' : '' }}>
                                <label class="form-check-label" for="kelas_edit_{{ $item->id }}_{{ $kelas->id }}">
                                    {{ $kelas->nama_kelas }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <small class="text-muted">Kosongkan jika semua kelas</small>
                </div>

                <div class="mb-3">
                    <label class="form-label">Ruang</label>
                    <select name="ruangan_id" class="form-select">
                        <option value="">-- Tidak ada Ruangan --</option> <!-- Opsi kosong -->
                        @foreach ($ruanganList as $ruang)
                        <option value="{{ $ruang->id }}" {{ $item->ruangan_id == $ruang->id ? 'selected' : '' }}>
                            {{ $ruang->nama }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="form-label">Keterangan (Opsional)</label>
                    <textarea name="keterangan" class="form-control" rows="3">{{ $item->keterangan }}</textarea>
                </div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endforeach