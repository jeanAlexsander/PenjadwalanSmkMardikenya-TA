@extends('layouts.admin')

@section('content')
<h1 class="mb-4">Profil Admin</h1>

<div class="row g-4"> <!-- Kartu Nama -->
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-primary text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user me-2"></i>Nama Lengkap</h5>
                <p class="card-text fs-5">Admin</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-secondary text-white">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user-shield me-2"></i>Role</h5>
                <p class="card-text fs-5">{{ ucfirst($user->role) }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card shadow-sm border-0 bg-dark text-white">
            <div class="card-body d-flex flex-column justify-content-between">
                <h5 class="card-title"><i class="fas fa-key me-2"></i>Ganti Password</h5>
                <button class="btn btn-sm btn-outline-light mt-0"
                    data-bs-toggle="modal"
                    data-bs-target="#modalGantiPassword">
                    <i class="fas fa-key me-1"></i> Ubah
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('modals')
@include('admin.profil.partials.modal-ganti-password')
@endpush