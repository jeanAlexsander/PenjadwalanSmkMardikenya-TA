{{-- Modal Tambah Guru --}}
<div class="modal fade" id="modalTambahGuru" tabindex="-1" aria-labelledby="modalTambahGuruLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.guru.store') }}" method="POST" class="modal-content rounded-3 shadow-lg border-0">
            @csrf
            <input type="hidden" name="asal_form" value="tambah">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Guru</h5>
            </div>
            <div class="modal-body">

                @php
                // Cek apakah sudah ada kepala sekolah aktif
                $kepsekExists = \App\Models\User::where('role','kepala_sekolah')->exists();
                @endphp

                {{-- Nama --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                        id="name" name="name" placeholder="Masukkan nama guru" required
                        value="{{ old('name') }}">
                    @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- NIP --}}
                <div class="mb-3">
                    <label for="nip" class="form-label">NIP</label>
                    <input type="text" class="form-control @error('nip') is-invalid @enderror"
                        id="nip" name="nip" placeholder="Masukkan NIP guru" required
                        value="{{ old('nip') }}">
                    @error('nip')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                        id="email" name="email" placeholder="Masukkan email guru" required
                        value="{{ old('email') }}">
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Role --}}
                <div class="mb-3">
                    <label for="role" class="form-label">Peran</label>
                    <select name="role" id="role" class="form-select @error('role') is-invalid @enderror" required>
                        <option value="guru" {{ old('role','guru')==='guru' ? 'selected' : '' }}>Guru</option>
                        <option value="kepala_sekolah"
                            {{ old('role')==='kepala_sekolah' ? 'selected' : '' }}
                            {{ $kepsekExists ? 'disabled' : '' }}>
                            Kepala Sekolah {{ $kepsekExists ? '(sudah ada)' : '' }}
                        </option>
                    </select>
                    @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Jenis Kelamin --}}
                <div class="mb-3">
                    <label for="jenis_kelamin" class="form-label">Jenis Kelamin</label>
                    <select class="form-select @error('jenis_kelamin') is-invalid @enderror"
                        id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="" disabled {{ old('jenis_kelamin') ? '' : 'selected' }}>Pilih jenis kelamin</option>
                        <option value="L" {{ old('jenis_kelamin') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('jenis_kelamin')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Alamat (opsional) --}}
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control @error('alamat') is-invalid @enderror"
                        id="alamat" name="alamat" rows="3" placeholder="Masukkan alamat guru (opsional)">{{ old('alamat') }}</textarea>
                    @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
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