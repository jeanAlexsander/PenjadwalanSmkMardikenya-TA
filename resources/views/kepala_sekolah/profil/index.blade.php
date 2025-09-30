@extends('layouts.kepalaSekolah')

@section('content')
<h1 class="mb-4">Profil Kepala Sekolah</h1>

<div class="row g-4">
    <!-- Kartu Nama -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user me-2"></i>Nama Lengkap</h5>
                <p class="card-text fs-5">{{ Auth::user()->guru->name }}</p>
            </div>
        </div>
    </div>

    <!-- Kartu NIP (ganti dari bg-dark -> bg-secondary agar unik) -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-secondary text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-id-badge me-2"></i>NIP</h5>
                <p class="card-text fs-5">{{ Auth::user()->guru->nip }}</p>
            </div>
        </div>
    </div>

    <!-- Kartu Email -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-info text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-envelope me-2"></i>Email</h5>
                <p class="card-text fs-5">{{ Auth::user()->guru->email }}</p>
            </div>
        </div>
    </div>

    <!-- Kartu Mata Pelajaran -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-warning text-dark">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-book me-2"></i>Mata Pelajaran</h5>
                @foreach (Auth::user()->guru->guruMapel ?? [] as $gm)
                <span class="badge bg-light text-dark me-1">
                    {{ $gm->mataPelajaran->nama_mata_pelajaran ?? 'Kosong' }}
                </span>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Kartu Alamat -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-light text-dark">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-map-marker-alt me-2"></i>Alamat</h5>
                <p class="card-text fs-5">{{ Auth::user()->guru->alamat ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Form Ganti Password (samakan dengan Guru) -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-dark text-white">
            <div class="card-body d-flex flex-column justify-content-between">
                <h5 class="card-title">
                    <i class="fas fa-key me-2"></i> Ganti Password
                </h5>
                <button class="btn btn-sm btn-light mt-0"
                    data-bs-toggle="modal"
                    data-bs-target="#modalGantiPassword">
                    <i class="fas fa-key me-1"></i> Ubah
                </button>
            </div>
        </div>
    </div>


    <!-- Kartu Kelas Diampu -->
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-success text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-chalkboard-teacher me-2"></i>Kelas yang Diampu</h5>
                <div class="row">
                    @forelse ($kelasDiampu as $kelas)
                    <div class="col-md-4 mb-2">
                        <span class="badge bg-light text-dark p-2 w-100">{{ $kelas->nama_kelas }}</span>
                    </div>
                    @empty
                    <div class="col-12">
                        <span class="text-white">Belum ada kelas yang diampu</span>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
@include('kepala_sekolah.profil.partials.modal-ganti-password')
@endpush