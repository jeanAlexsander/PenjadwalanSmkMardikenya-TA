@extends('layouts.admin')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    {{-- Judul Halaman --}}
    <h1 class="mb-4">
        Kebutuhan Kelas Mapel
        @if($kelasTerpilih)
        <small class="text-muted">— {{ $kelasTerpilih->nama_kelas }}</small>
        @endif
    </h1>

    {{-- Flash --}}
    @if(session('toast_success'))
    <div class="alert alert-success">{{ session('toast_success') }}</div>
    @endif
    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
        </ul>
    </div>
    @endif

    {{-- Filter Kelas + Aksi --}}
    <div class="row align-items-center g-2 mb-4">
        <div class="col-md-auto">
            <div class="btn-group">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    {{ $kelasTerpilih?->nama_kelas ?? 'Pilih Kelas' }}
                </button>
                <ul class="dropdown-menu">
                    @foreach ($listKelas as $kelasItem)
                    <li>
                        <a class="dropdown-item {{ request('kelas') == $kelasItem->nama_kelas ? 'active' : '' }}"
                            href="{{ route('admin.kebutuhan-mapel-kelas.index', ['kelas' => $kelasItem->nama_kelas]) }}">
                            {{ $kelasItem->nama_kelas }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="col-md-auto">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambahKebutuhan">
                <i class="fas fa-plus me-1"></i> Tambah Kebutuhan
            </button>
        </div>
    </div>

    {{-- Tabel (flex-grow-1 mendorong pagination ke bawah) --}}
    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered text-nowrap text-center align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Guru</th>
                        <th>Jenis</th>
                        <th>JP/Minggu</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kebutuhan as $i => $item)
                    <tr>
                        {{-- nomor lanjut per halaman --}}
                        <td>{{ ($kebutuhan->firstItem() ?? 0) + $i }}</td>

                        <td>{{ $item->kelas->nama_kelas ?? '-' }}</td>

                        <td>
                            {{ $item->guruMapel->mataPelajaran->nama_mata_pelajaran
                               ?? $item->guruMapel->mataPelajaran->nama
                               ?? '-' }}
                        </td>

                        <td>
                            {{ $item->guruMapel->guru->user->name
                               ?? $item->guruMapel->guru->name
                               ?? '-' }}
                        </td>

                        <td>
                            <span class="badge bg-secondary">
                                {{ strtoupper($item->guruMapel->jenis ?? '-') }}
                            </span>
                        </td>

                        <td>{{ $item->jumlah_jam_per_minggu }}</td>

                        <td>
                            <div class="d-inline-flex flex-wrap gap-2 justify-content-center">
                                <button class="btn btn-sm btn-warning"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEditKebutuhan{{ $item->id }}">
                                    <i class="fas fa-edit me-1"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalHapusKebutuhan{{ $item->id }}">
                                    <i class="fas fa-trash me-1"></i> Hapus
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center table-warning">
                            Belum ada data kebutuhan untuk kelas ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah (bukan fixed) --}}
    @if ($kebutuhan->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $kebutuhan->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection

@push('modals')
@include('admin.kebutuhan_kelas_mapel.partials.modal-tambah')
@include('admin.kebutuhan_kelas_mapel.partials.modal-edit')
@include('admin.kebutuhan_kelas_mapel.partials.modal-hapus')
@endpush