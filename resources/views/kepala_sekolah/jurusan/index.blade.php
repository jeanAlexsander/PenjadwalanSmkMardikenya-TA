@extends('layouts.kepalaSekolah')

@section('title', 'Data Jurusan Sekolah')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    {{-- Judul Halaman --}}
    <h1 class="mb-4">Data Jurusan Sekolah</h1>

    {{-- Tabel Data Jurusan --}}
    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Nama Jurusan</th>
                        <th class="text-center">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($dataJurusan as $index => $jurusan)
                    <tr>
                        {{-- nomor lanjut per halaman --}}
                        <td class="text-center">{{ ($dataJurusan->firstItem() ?? 0) + $index }}</td>
                        <td class="text-center">{{ $jurusan->nama_jurusan }}</td>
                        <td class="text-center" style="white-space: normal;">
                            {{ $jurusan->keterangan }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center table-warning">
                            Belum ada data jurusan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah container --}}
    @if ($dataJurusan->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $dataJurusan->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection