@extends('layouts.kepalaSekolah')

@section('title', 'Data Kelas')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    {{-- Judul Halaman --}}
    <h1 class="mb-4">Data Kelas</h1>

    <div class="flex-grow-1">
        <div class="table-responsive">
            {{-- Tabel Data --}}
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr class="text-center">
                        <th>No</th>
                        <th>Nama Kelas</th>
                        <th>Jurusan</th>
                        <th>Tingkat</th>
                        <th>Wali Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kelases as $index => $kls)
                    <tr class="text-center">
                        {{-- nomor lanjut per halaman --}}
                        <td>{{ ($kelases->firstItem() ?? 0) + $index }}</td>

                        <td>{{ $kls->nama_kelas }}</td>
                        <td>{{ $kls->jurusan->nama_jurusan ?? '-' }}</td>
                        <td>{{ $kls->tingkat }}</td>
                        <td>{{ $kls->waliKelas->user->name ?? $kls->waliKelas->name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center table-warning">Belum ada data kelas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah container --}}
    @if ($kelases->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $kelases->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection