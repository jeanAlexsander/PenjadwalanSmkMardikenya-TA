@extends('layouts.admin')

@section('content')
<div class="container-fluid page-shell"> {{-- shell flex full height --}}
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="mb-4">Histori Jadwal</h1>
    </div>

    <div class="flex-grow-1">
        <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:180px">Waktu</th>
                        <th style="width:140px">Langkah</th>
                        <th style="width:120px">Total Baris</th>
                        <th style="width:220px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($batches as $b)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($b->waktu_aksi)->setTimezone('Asia/Jakarta')->format('d M Y H:i') }}</td>
                        <td><span class="badge bg-secondary">{{ $b->aksi }}</span></td>
                        <td>{{ $b->total }}</td>
                        <td>
                            <div class="d-flex justify-content-center gap-2">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.histori.show', $b->batch_key) }}">
                                    <i class="fas fa-eye me-1"></i> Detail
                                </a>
                                <a class="btn btn-sm btn-success" href="{{ route('admin.histori.pdf', $b->batch_key) }}">
                                    <i class="fas fa-download me-1"></i> Download PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="table-warning">Belum ada histori.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination: nempel di bawah shell (bukan fixed) --}}
    @if ($batches->hasPages())
    <div class="mt-auto pt-3 border-top bg-white">
        <div class="d-flex justify-content-center">
            {!! $batches->links() !!}
        </div>
    </div>
    @endif
</div>
@endsection