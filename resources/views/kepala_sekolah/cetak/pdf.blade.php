<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Jadwal Pelajaran</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            font-size: 11px;
            margin: 16px;
        }

        .text-center {
            text-align: center;
        }

        .text-end {
            text-align: right;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        .table th,
        .table td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
        }

        .table th {
            background-color: #f2f2f2;
        }

        .page-break {
            page-break-after: always;
        }

        .signature-space {
            height: 50px;
        }

        h3,
        h4 {
            margin: 15mm;
        }
    </style>
</head>

<body>

    @foreach ($dataCetak as $kelasData)
    <div>
        <div class="text-center">
            <img src="{{ public_path('images/logo.jpg') }}" alt="Logo Sekolah" style="width: 80px; margin-bottom: 10px;">
            <h1>JADWAL PELAJARAN SMK MARDIKENYA</h1>
            <h2>Kelas {{ $kelasData['kelas'] }}</h2>
            <hr>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th style="width: 70px;">Jam</th>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                    <th>{{ $hari }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @for ($jam = 0; $jam <= 12; $jam++)
                    @if ($jam===4 || $jam===8)
                    <tr style="background-color: #f0f0f0;">
                    <td><strong>
                            @if ($jam === 4)
                            Istirahat 1<br><small>09:45 - 10:15</small>
                            @else
                            Istirahat 2<br><small>12:15 - 12:50</small>
                            @endif
                        </strong></td>
                    @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                    <td class="text-center">-</td>
                    @endforeach
                    </tr>
                    @else
                    <tr>
                        <td><strong>{{ $jamWaktu[$jam] ?? 'Jam ' . $jam }}</strong></td>
                        @foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $hari)
                        @php
                        $cell = $kelasData['jadwal'][$hari][$jam] ?? null;
                        @endphp
                        <td>
                            @if ($cell)
                            <div><strong>{{ $cell['mapel'] ?? '' }}</strong></div>
                            <div><small>{{ $cell['guru'] ?? '' }} - {{ $cell['ruang'] ?? '' }}</small></div>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endif
                    @endfor
            </tbody>
        </table>


        <div class="text-end" style="margin-top: 60px;">
            <p>Purwokerto, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Kepala Sekolah</p>
            <div class="signature-space"></div>
            <p><strong>{{ $kepalaSekolah->nama }}</strong></p>
        </div>
    </div>

    @if (!$loop->last)
    <div class="page-break"></div>
    @endif
    @endforeach

</body>

</html>