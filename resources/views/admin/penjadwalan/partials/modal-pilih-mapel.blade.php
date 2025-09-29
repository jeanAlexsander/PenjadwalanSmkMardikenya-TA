<div class="modal fade" id="modalPilihMapel" tabindex="-1" aria-labelledby="modalKonfirmasiHapusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form id="formTambahJadwal" method="POST" action="{{ route('admin.penjadwalan.store') }}">
                @csrf
                {{-- untuk EDIT, nanti JS akan set _method=PUT dan ganti action ke route update --}}
                <input type="hidden" name="_method" id="methodOverride" value="POST">

                <input type="hidden" name="jadwal_id" id="inputJadwalId">
                <input type="hidden" name="hari" id="inputHari"> {{-- isi angka 1..6 dari data-hari --}}
                <input type="hidden" name="jam" id="inputJam"> {{-- isi jam-ke dari data-jam --}}
                <input type="hidden" name="kelas_id" id="inputKelasId" value="{{ $kelasTerpilih->id ?? '' }}">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Mapel ke Kelas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">- Pilih Mata Pelajaran & Guru</label>

                        @forelse ($guruMapel as $item)
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                id="gp{{ $item->id }}"
                                name="guru_mata_pelajaran_id" {{-- ganti: guru_mapel_id -> guru_mata_pelajaran_id --}}
                                value="{{ $item->id }}"
                                data-jenis="{{ $item->jenis }}"> {{-- TEORI / PRAKTIKUM --}}
                            <label class="form-check-label" for="gp{{ $item->id }}">
                                {{ optional($item->mataPelajaran)->nama_mata_pelajaran ?? optional($item->mataPelajaran)->nama ?? 'Mapel Tidak Dikenali' }}
                                -
                                {{ optional($item->guru)->user->name ?? optional($item->guru)->name ?? 'Guru Tidak Dikenali' }}
                                <span class="badge bg-secondary">{{ strtoupper($item->jenis) }}</span>
                            </label>
                        </div>
                        @empty
                        <div class="text-danger">Belum ada data guru & mapel.</div>
                        @endforelse
                    </div>

                    <div class="mb-3 mt-3">
                        <label for="ruangan_id" class="form-label">- Pilih Ruangan</label>
                        <select class="form-select" name="ruangan_id" id="ruangan_id"> {{-- ganti: ruang_id -> ruangan_id --}}
                            <option value="">-- Pilih Ruangan --</option>
                            @foreach ($ruangan as $ruang)
                            <option value="{{ $ruang->id }}">{{ $ruang->nama }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted" id="ruangNote"></small>
                    </div>
                </div>

                <div class="modal-footer border-0">
                    <div class="container">
                        <div class="row gx-2">
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>