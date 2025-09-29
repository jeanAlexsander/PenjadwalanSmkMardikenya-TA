{{-- Modal Tambah Jadwal Khusus --}}
<div class="modal fade" id="modalTambahKegiatan" tabindex="-1" aria-labelledby="modalTambahKegiatanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" action="{{ route('admin.jadwal_khusus.store') }}" method="POST">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahKegiatanLabel">Tambah Jadwal Khusus</h5>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                    <input type="text" id="nama_kegiatan" name="nama_kegiatan" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="tanggal" class="form-label">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="jam_mulai" class="form-label">Jam Mulai</label>
                    <input type="time" id="jam_mulai" name="jam_mulai" class="form-control">
                </div>

                <div class="mb-3">
                    <label for="jam_selesai" class="form-label">Jam Selesai</label>
                    <input type="time" id="jam_selesai" name="jam_selesai" class="form-control">
                </div>

                @php $prefix = 'new'; @endphp
                <div class="form-check mb-3">
                    <input type="checkbox"
                        name="untuk_semua_kelas"
                        id="untuk_semua_kelas_{{ $prefix }}"
                        class="form-check-input js-semua-kelas"
                        data-target="#kelas-container-{{ $prefix }}"
                        {{ old('untuk_semua_kelas') ? 'checked' : '' }}>
                    <label class="form-check-label" for="untuk_semua_kelas_{{ $prefix }}">
                        Untuk Semua Kelas
                    </label>
                </div>

                <div class="mb-3">
                    <label class="form-label">Kelas</label>
                    <div class="row" id="kelas-container-{{ $prefix }}">
                        @foreach ($kelasList as $kelas)
                        <div class="col-md-4 col-sm-6">
                            <div class="form-check">
                                <input type="checkbox"
                                    name="kelas_id[]"
                                    value="{{ $kelas->id }}"
                                    id="kelas_{{ $prefix }}_{{ $kelas->id }}"
                                    class="form-check-input"
                                    {{ in_array($kelas->id, (array) old('kelas_id', []), true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="kelas_{{ $prefix }}_{{ $kelas->id }}">
                                    {{ $kelas->nama_kelas }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <small class="text-muted">Kosongkan jika semua kelas</small>
                </div>

                <div class="mb-3">
                    <label for="ruangan_id" class="form-label">Ruang</label>
                    <select name="ruangan_id" id="ruangan_id" class="form-select">
                        <option value="">-- Tidak ada Ruangan --</option> <!-- Tambahan ini -->
                        @foreach ($ruanganList as $ruang)
                        <option value="{{ $ruang->id }}">{{ $ruang->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="keterangan" class="form-label">Keterangan (Opsional)</label>
                    <textarea id="keterangan" name="keterangan" class="form-control" placeholder="Tulis keterangan kegiatan" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Batal
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>