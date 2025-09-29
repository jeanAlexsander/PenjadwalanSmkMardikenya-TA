@extends('layouts.kepalaSekolah')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    {{-- Judul Halaman --}}
    <h1 class='mb-4'>Data Mata Pelajaran</h1>

    {{-- Area tabel mendorong pagination ke bawah --}}
    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama Mata Pelajaran</th>
                        <th>Kode</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($mataPelajaran as $index => $item)
                    <tr>
                        {{-- nomor lanjut per halaman --}}
                        <td>{{ ($mataPelajaran->firstItem() ?? 0) + $index }}</td>
                        <td>{{ $item->nama_mata_pelajaran }}</td>
                        <td>{{ $item->kode_mata_pelajaran }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center table-warning">
                            Belum ada data mata pelajaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah container --}}
    @if ($mataPelajaran->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $mataPelajaran->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection