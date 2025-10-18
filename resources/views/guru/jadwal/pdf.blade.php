<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Jadwal Mengajar</title>
    <style>
        * {
            font-family: DejaVu Sans, Arial, sans-serif;
        }

        body {
            font-size: 12px;
            color: #000;
        }

        h1 {
            text-align: center;
            margin-bottom: 5px;
        }

        h3 {
            margin: 15px 0 5px;
        }

        .meta {
            margin-bottom: 10px;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: center;
        }

        th {
            background: #f2f2f2;
            font-weight: bold;
        }

        tr:nth-child(even) td {
            background: #fafafa;
        }

        thead {
            display: table-header-group;
        }

        tfoot {
            display: table-row-group;
        }

        @page {
            margin: 20mm 15mm 20mm 15mm;
        }
    </style>
</head>

<body>
    <h1>JADWAL MENGAJAR</h1>
    <div class="meta">
        Guru: <strong>{{ $guruNama ?? '-' }}</strong><br>
        Dicetak: {{ $dicetakPada ?? now()->format('d M Y H:i') }}
    </div>

    @php
    // mapping jam ke → waktu sebenarnya
    $jamRange = [
    0 => '07:00 - 07:30',
    1 => '07:30 - 08:10',
    2 => '08:10 - 08:50',
    3 => '08:50 - 09:30',
    4 => '09:30 - 10:00', // Istirahat
    5 => '10:00 - 10:40',
    6 => '10:40 - 11:20',
    7 => '11:20 - 12:00',
    8 => '12:00 - 12:50', // Istirahat
    9 => '12:50 - 13:30',
    10 => '13:30 - 14:10',
    11 => '14:10 - 14:50',
    12 => '14:50 - 15:30',
    ];

    $grouped = collect($jadwal)->groupBy('hari_label');
    $urutan = ['Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    @endphp

    @foreach ($urutan as $h)
    @if ($grouped->has($h))
    <h3>{{ $h }}</h3>
    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Mata Pelajaran</th>
                <th>Kelas</th>
                <th>Ruangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($grouped[$h]->sortBy('jam') as $it)
            <tr>
                @php
                $jam = $it->jam ?? 0;
                $rentang = $jamRange[$jam] ?? ('Jam ' . $jam);
                @endphp
                <td>{{ $rentang }}</td>
                <td>{{ $it->mapel ?? '-' }}</td>
                <td>{{ $it->kelas ?? '-' }}</td>
                <td>{{ $it->ruang ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
    @endforeach

    @if ($grouped->isEmpty())
    <p><em>Belum ada jadwal mengajar.</em></p>
    @endif
</body>

</html>