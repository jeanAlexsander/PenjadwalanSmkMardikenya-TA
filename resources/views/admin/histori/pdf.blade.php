@php
$hariLabel = [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat',6=>'Sabtu'];

$kelasNames = \App\Models\Kelas::pluck('nama_kelas','id'); // [id => nama]
$ruanganNames = \App\Models\Ruangan::pluck('nama','id'); // [id => nama]

// Ambil hanya ID yang muncul di batch ini
$gmpIds = $rows->pluck('guru_mata_pelajaran_id')->filter()->unique()->values();

$gmpNames = collect();
if ($gmpIds->isNotEmpty()) {
$gmps = \App\Models\GuruMapel::with([
'mataPelajaran:id,nama_mata_pelajaran',
'guru:id,name', // nama ada di kolom 'name' tabel gurus
])
->whereIn('id', $gmpIds)
->get();

$gmpNames = $gmps->mapWithKeys(function ($gmp) {
$mapel = $gmp->mataPelajaran?->nama_mata_pelajaran ?? 'MAPEL';
$guru = $gmp->guru?->name ?? '(guru belum diatur)';
return [$gmp->id => "{$mapel} – {$guru}"];
});
}

$byKelas = $rows->groupBy('kelas_id');
$jamMin = max(0, (int)$rows->min('jam'));
$jamMax = max(12,(int)$rows->max('jam'));
@endphp
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Histori Jadwal — {{ $meta['batch'] }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
        }

        h2,
        h3 {
            margin: 2px 0 6px;
        }

        .meta {
            color: #666;
            font-size: 10px;
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 4px 6px;
            vertical-align: top;
        }

        thead th {
            background: #f3f3f3;
        }

        .kelas-title {
            margin: 10px 0 6px;
        }

        .badge {
            display: inline-block;
            padding: 1px 6px;
            border-radius: 8px;
            border: 1px solid #999;
            font-size: 10px;
        }

        .note {
            font-size: 10px;
            color: #666;
            margin-top: 6px;
        }

        .pagebreak {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <style>
        /* Gaya minimal untuk PDF (DomPDF) */
        * {
            font-family: DejaVu Sans, sans-serif;
        }

        h2 {
            margin: 0 0 6px 0;
        }

        .meta {
            font-size: 12px;
            color: #555;
            margin-bottom: 10px;
        }

        .kelas-title {
            margin: 14px 0 6px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: #f2f2f2;
        }

        th,
        td {
            border: 1px solid #444;
            padding: 6px;
            text-align: center;
            vertical-align: middle;
        }

        th.col-jam {
            width: 42px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            background: #eee;
            border: 1px solid #bbb;
            font-size: 11px;
        }

        .cell-non {
            background: #fafafa;
            font-weight: 600;
        }

        .note {
            font-size: 11px;
            color: #666;
            margin-top: 6px;
        }

        .pagebreak {
            page-break-after: always;
        }
    </style>

    <h2>Histori Jadwal</h2>
    <div class="meta">Waktu aksi: {{ $meta['waktu'] }} WIB</div>

    @foreach ($byKelas as $kelasId => $items)
    <h3 class="kelas-title">Kelas: {{ $kelasNames[$kelasId] ?? $kelasId }}</h3>

    @php
    // fallback bila variabel tidak dikirim
    $jmMin = isset($jamMin) ? (int)$jamMin : 0;
    $jmMax = isset($jamMax) ? (int)$jamMax : 12;
    $label = $hariLabel ?? [1=>'Senin',2=>'Selasa',3=>'Rabu',4=>'Kamis',5=>'Jumat'];

    // Map kegiatan Jam 0 per hari (1=Senin s.d. 5=Jumat)
    $kegiatanJamNol = [
    1 => 'UPACARA',
    2 => 'KEBERSIHAN',
    3 => 'LITERASI KEAGAMAAN',
    4 => 'LITERASI UMUM',
    5 => 'SENAM & KEBERSIHAN',
    ];

    // Inisialisasi grid [jam][hari] => string
    $grid = [];
    for ($j=$jmMin; $j<=$jmMax; $j++) {
        for ($h=1; $h<=6; $h++) { $grid[$j][$h]='' ; }
        }

        // Isi grid: "Mapel – Guru" dan kalau ada ruangan → baris baru "@ Ruangan"
        foreach ($items as $r) {
        $text=$r->jenis === 'MAPEL'
        ? ($gmpNames[$r->guru_mata_pelajaran_id] ?? 'MAPEL')
        : $r->jenis;

        $ruang = $ruanganNames[$r->ruangan_id ?? 0] ?? null;
        if ($ruang) $text .= "\n " . $ruang;

        // Pastikan indeks valid
        if (isset($grid[$r->jam][$r->hari])) {
        $grid[$r->jam][$r->hari] = $text;
        }
        }
        @endphp

        <table>
            <thead>
                <tr>
                    <th class="col-jam">Jam</th>
                    @for ($h=1; $h<=5; $h++)
                        <th>{{ $label[$h] ?? $h }}</th>
                        @endfor
                </tr>
            </thead>
            <tbody>
                @for ($j=$jmMin; $j<=$jmMax; $j++)
                    <tr>
                    <th>{{ $j === 0 ? 'Jam 0' : 'Ke-'.$j }}</th>
                    @for ($h=1; $h<=5; $h++)
                        @php
                        $val=$grid[$j][$h] ?? '' ;
                        // Tentukan isi & kelas sel
                        $isNon=in_array($val, ['ISTIRAHAT','EKSKUL','UPACARA','KEGIATAN'], true);

                        if ($j===0) {
                        // Jam Nol selalu pakai mapping per hari
                        $val=$kegiatanJamNol[$h] ?? 'KEGIATAN' ;
                        $isNon=true;
                        }
                        elseif (in_array($j, [4,8], true) && $val==='' ) {
                        // Jam istirahat default bila kosong
                        $val='ISTIRAHAT' ;
                        $isNon=true;
                        }
                        @endphp

                        <td class="{{ $isNon ? 'cell-non' : '' }}">
                        {!! $val !== '' ? nl2br(e($val)) : '<span class="badge">&mdash;</span>' !!}
                        </td>
                        @endfor
                        </tr>
                        @endfor
            </tbody>
        </table>

        <div class="note">
            Sel menampilkan <em>Mapel – Guru</em> dan, bila ada, baris baru <em>@ Ruangan</em>.
            Kegiatan non-mapel (ISTIRAHAT/EKSKUL/UPACARA/KEGIATAN) diberi latar halus.
        </div>

        @if (! $loop->last)
        <div class="pagebreak"></div>
        @endif
        @endforeach
</body>


</html>