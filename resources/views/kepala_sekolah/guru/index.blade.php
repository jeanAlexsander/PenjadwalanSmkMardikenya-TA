@extends('layouts.kepalaSekolah')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    {{-- Judul Halaman --}}
    <h1 class="mb-4">Data Guru</h1>

    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>NIP</th>
                        <th>Email</th>
                        <th>Jenis kelamin</th>
                        <th>Alamat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($guru as $index => $item)
                    <tr>
                        {{-- nomor lanjut per halaman --}}
                        <td>{{ ($guru->firstItem() ?? 0) + $index }}</td>

                        {{-- dukung object (model) dan array --}}
                        <td>{{ $item->name ?? $item['name'] ?? '-' }}</td>
                        <td>{{ $item->nip ?? $item['nip'] ?? '-' }}</td>
                        <td>{{ $item->email ?? $item['email'] ?? '-' }}</td>
                        <td>{{ $item->jenis_kelamin ?? $item['jenis_kelamin'] ?? '-' }}</td>
                        <td>{{ $item->alamat ?? $item['alamat'] ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center table-warning">Belum ada data guru.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah container --}}
    @if ($guru->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $guru->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection