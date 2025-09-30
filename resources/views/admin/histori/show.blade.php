@extends('layouts.admin')

@section('content')
@php
$kelasNames = \App\Models\Kelas::pluck('nama_kelas','id');
$ruanganNames = \App\Models\Ruangan::pluck('nama','id');

$gmpIds = $rows->pluck('guru_mata_pelajaran_id')->filter()->unique();

$gmpNames = \App\Models\GuruMapel::with([
'mataPelajaran:id,nama_mata_pelajaran',
'guru',
])
->whereIn('id', $gmpIds)
->get()
->mapWithKeys(function ($gmp) {
$mapel = $gmp->mataPelajaran?->nama_mata_pelajaran ?? 'MAPEL';
$guru = $gmp->guru?->name
?? $gmp->guru?->nama
?? $gmp->guru?->full_name
?? '(guru belum diatur)';
return [$gmp->id => "{$mapel} – {$guru}"];
});

$hariLabel = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'];
@endphp

<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">Detail Histori</h4>
        <div>
            <a class="btn btn-success" href="{{ route('admin.histori.pdf', $batch) }}">
                <i class="fas fa-download me-1"></i> Download PDF
            </a>
            <a class="btn btn-outline-secondary" href="{{ route('admin.histori.index') }}">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:140px">Kelas</th>
                        <th style="width:90px">Hari</th>
                        <th style="width:80px">Jam</th>
                        <th style="width:140px">Jenis</th>
                        <th>Mapel dan Guru</th>
                        <th style="width:160px">Ruangan</th>
                        <th style="width:180px">Waktu Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                    <tr>
                        <td>{{ $kelasNames[$r->kelas_id] ?? $r->kelas_id }}</td>
                        <td>{{ $hariLabel[$r->hari] ?? $r->hari }}</td>
                        <td>Ke-{{ $r->jam }}</td>
                        <td><span class="badge bg-info">{{ $r->jenis }}</span></td>
                        <td>
                            @if ($r->jenis === 'MAPEL')
                            {{ $gmpNames[$r->guru_mata_pelajaran_id]
                   ?? $r->snapshot_text
                   ?? 'MAPEL – (guru/mapel tidak ditemukan)' }}
                            @else
                            —
                            @endif
                        </td>
                        <td>{{ $ruanganNames[$r->ruangan_id ?? 0] ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($r->waktu_aksi)->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center table-warning">Belum Ada data.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah shell (bukan fixed) --}}
    @if ($rows->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $rows->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection