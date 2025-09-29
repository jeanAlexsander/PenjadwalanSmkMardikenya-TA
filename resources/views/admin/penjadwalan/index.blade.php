    @extends('layouts.admin')

    @section('content')

    <div class="container-fluid">
        {{-- Judul Halaman --}}
        <h1 class="mb-4">Penjadwalan Kelas: {{ $kelasTerpilih->nama_kelas ?? '-' }}</h1>

        @php
        // Normalisasi preview dari controller -> aman walau null
        $previewRowsCol = collect($previewRows ?? []);
        $hasPreview = $previewRowsCol->isNotEmpty();
        $mapHariNum = ['Senin'=>1,'Selasa'=>2,'Rabu'=>3,'Kamis'=>4,'Jumat'=>5];
        $hariList = ['Senin','Selasa','Rabu','Kamis','Jumat'];

        if (!function_exists('hitungJam')) {
        function hitungJam($jamKe) {
        $waktu = \Carbon\Carbon::createFromTime(7, 0);
        $durasi = [
        0 => 30, 1 => 40, 2 => 40, 3 => 40,
        4 => 30, // Istirahat 1
        5 => 40, 6 => 40, 7 => 40,
        8 => 50, // Istirahat 2
        9 => 40, 10 => 40, 11 => 40, 12 => 40,
        ];
        for ($i = 0; $i <= 12; $i++) {
            $mulai=$waktu->copy();
            $waktu->addMinutes($durasi[$i]);
            if ($i === $jamKe) {
            return ['mulai'=>$mulai->format('H:i'),'selesai'=>$waktu->format('H:i')];
            }
            }
            return ['mulai'=>'00:00','selesai'=>'00:00'];
            }
            }

            $jumlahJamPerHari = ['Senin'=>12,'Selasa'=>12,'Rabu'=>12,'Kamis'=>12,'Jumat'=>10];
            $maxJam = max($jumlahJamPerHari);
            $jam0Labels = [
            1 => 'Upacara',
            2 => 'Literasi Agama',
            3 => 'Kebersihan',
            4 => 'Literasi Umum',
            5 => 'Senam & Kebersihan',
            ];
            @endphp

            {{-- Kontrol Atas --}}
            <div class="row align-items-center mb-4 g-2">
                <div class="col-md-auto">
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            Pilih Kelas Lain
                        </button>
                        <ul class="dropdown-menu">
                            @foreach ($listKelas as $kelasItem)
                            <li>
                                <a class="dropdown-item {{ request('kelas') == $kelasItem->nama_kelas ? 'active' : '' }}"
                                    href="{{ route('admin.penjadwalan.index', [
                                            'kelas' => $kelasItem->nama_kelas,
                                            'preview_key' => $previewKey,   // <-- bawa key preview
                                          ]) }}">
                                    {{ $kelasItem->nama_kelas }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <div class="col-md-auto d-flex gap-2">

                    {{-- Generate PER KELAS --}}
                    <form action="{{ route('admin.penjadwalan.generate') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="mode" value="kelas">
                        @if($kelasTerpilih)
                        <input type="hidden" name="kelas_ids[]" value="{{ $kelasTerpilih->id }}">
                        @endif
                        <button type="submit"
                            class="btn btn-outline-primary"
                            {{ (!$kelasTerpilih || $isLocked) ? 'disabled' : '' }}
                            title="{{ $kelasTerpilih
                            ? ($isLocked ? 'Jadwal kelas ini sudah tersimpan. Reset dulu untuk generate ulang.' : 'Generate otomatis untuk kelas terpilih')
                            : 'Pilih kelas terlebih dahulu' }}">
                            <i class="fas fa-cogs me-1"></i> Generate Kelas Ini
                        </button>
                    </form>

                    {{-- Generate GLOBAL --}}
                    <form action="{{ route('admin.penjadwalan.generate') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="mode" value="global">

                        {{-- kirim kelas yang sedang dibuka agar tetap stay di sana --}}
                        @if($kelasTerpilih)
                        <input type="hidden" name="current_kelas_id" value="{{ $kelasTerpilih->id }}">
                        <input type="hidden" name="current_kelas_nama" value="{{ $kelasTerpilih->nama_kelas }}">
                        @endif

                        <button type="submit" class="btn btn-outline-secondary" title="Generate otomatis untuk semua kelas (global)">
                            <i class="fas fa-globe me-1"></i> Generate Global
                        </button>
                    </form>

                </div>
            </div>

            {{-- Banner Preview (jika ada) --}}
            @if ($hasPreview)
            @php
            // Pastikan key tersedia untuk kedua tombol
            $key = $previewKey ?? request('preview_key') ?? session('ga_preview_key');
            @endphp
            <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <strong>Preview jadwal belum disimpan</strong>
                </div>

                <div class="d-flex gap-2">
                    {{-- Tombol Batal Preview (hapus SEMUA kelas pada batch ini) --}}
                    <form action="{{ route('admin.penjadwalan.batal') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="preview_key" value="{{ $key }}">
                        <button type="submit" class="btn btn-danger btn-sm" @disabled(empty($key))>
                            <i class="fas fa-times me-1"></i> Batal Preview
                        </button>
                    </form>

                    {{-- Tombol Simpan Jadwal (commit preview ke DB) --}}
                    <form action="{{ route('admin.penjadwalan.simpan') }}" method="POST" class="d-inline">
                        @csrf
                        <input type="hidden" name="cache_key" value="{{ $key }}">
                        <button type="submit" class="btn btn-primary btn-sm" @disabled(empty($key))>
                            <i class="fas fa-save me-1"></i> Simpan Jadwal
                        </button>
                    </form>
                </div>
            </div>
            @endif


            @if (!$kelasTerpilih)
            <div class="alert alert-warning text-center">
                Silakan pilih kelas terlebih dahulu sebelum menambahkan jadwal.
            </div>
            @else
            {{-- Tabel Jadwal --}}
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>Jam</th>
                            <th>Senin</th>
                            <th>Selasa</th>
                            <th>Rabu</th>
                            <th>Kamis</th>
                            <th>Jumat</th>
                        </tr>
                    </thead>

                    @php
                    // Palet warna pastel (aman utk teks gelap)
                    $MAPEL_PALETTE = [
                    '#E3F2FD', '#E8F5E9', '#FFF3E0', '#F3E5F5', '#E0F7FA',
                    '#FCE4EC', '#F1F8E9', '#FFFDE7', '#EDE7F6', '#E0E0E0',
                    ];

                    // Hindari redeclare saat view di-include berulang
                    if (!function_exists('colorForMapel')) {
                    function colorForMapel(?string $mapel, array $palette): string {
                    if (!$mapel || strtoupper($mapel) === 'MAPEL KOSONG') return '#FFFFFF';
                    $key = mb_strtolower(trim($mapel));
                    $idx = abs(crc32($key)) % count($palette);
                    return $palette[$idx];
                    }
                    }

                    if (!function_exists('colorForJenis')) {
                    function colorForJenis(?string $jenis): ?string {
                    return match ($jenis) {
                    'UPACARA' => '#D1E7DD', // hijau lembut
                    'EKSKUL' => '#CCE5FF', // biru lembut
                    'KEGIATAN' => '#FFE5D0', // oranye lembut
                    default => null,
                    };
                    }
                    }

                    $mapHariNum = ['Senin'=>1,'Selasa'=>2,'Rabu'=>3,'Kamis'=>4,'Jumat'=>5];
                    $jam0Labels = [
                    1 => 'Upacara',
                    2 => 'Kebersihan',
                    3 => 'Literasi Keagamaan',
                    4 => 'Literasi Umum',
                    5 => 'Senam & Kebersihan',
                    ];
                    @endphp


                    <tbody id="tabelJadwal">
                        @for ($i = 0; $i <= $maxJam; $i++)
                            @php $waktu=hitungJam($i); @endphp

                            @if ($i===4 || $i===8)
                            {{-- Baris Istirahat --}}
                            <tr class="bg-warning bg-opacity-25 text-center align-middle">
                            <td>
                                <div class="fw-semibold">Jam {{ $i }}</div>
                                <div class="small text-muted">{{ $waktu['mulai'] }} - {{ $waktu['selesai'] }}</div>
                            </td>
                            @foreach ($hariList as $hari)
                            @if ($i < $jumlahJamPerHari[$hari])
                                <td class="text-center align-middle text-muted"><strong>Istirahat</strong></td>
                                @else
                                <td></td>
                                @endif
                                @endforeach
                                </tr>
                                @else
                                {{-- Baris Jadwal --}}
                                <tr>
                                    <td class="text-center align-middle">
                                        <div><strong>Jam {{ $i }}</strong></div>
                                        <div class="small text-muted">{{ $waktu['mulai'] }} - {{ $waktu['selesai'] }}</div>
                                    </td>

                                    @foreach ($hariList as $hari)
                                    @if ($i <= $jumlahJamPerHari[$hari])
                                        @php
                                        // Data dari DB untuk sel ini
                                        $data=$jadwal->first(fn($j) => (int)$j->hari === $mapHariNum[$hari] && (int)$j->jam === (int)$i);

                                        // Preview (kalau ada) untuk sel ini
                                        $pv = $previewRows?->first(fn($r) => (int)$r['hari'] === $mapHariNum[$hari] && (int)$r['jam'] === (int)$i);
                                        @endphp

                                        <td class="td-jadwal position-relative" style="min-width:160px;min-height:70px">
                                            {{-- === PILIHAN TAMPILAN: PREVIEW > DB > KOSONG === --}}
                                            @if ($pv)
                                            {{-- PREVIEW --}}
                                            @php
                                            $pvJenis = $pv['jenis'] ?? 'MAPEL';
                                            $pvMapelLabel = $pv['_mapel_label'] ?? ($pv['mapel_nama'] ?? null);

                                            $pvJenisClass = match($pvJenis) {
                                            'UPACARA' => 'bg-upacara',
                                            'EKSKUL' => 'bg-ekskul',
                                            'KEGIATAN'=> 'bg-kegiatan',
                                            default => null,
                                            };

                                            if ($pvJenisClass) {
                                            $pvClass = $pvJenisClass;
                                            } else {
                                            $key = mb_strtolower(trim((string)$pvMapelLabel));
                                            $idx = abs(crc32($key)) % 10; // 0..9
                                            $pvClass = 'bg-mapel-' . $idx;
                                            }
                                            @endphp

                                            <div class="card-slot {{ $pvClass }}">
                                                @if($pvJenis !== 'MAPEL')
                                                <span class="badge bg-secondary">{{ $pvJenis }}</span>
                                                @if((int)($pv['jam'] ?? -1) === 0)
                                                <div class="fw-semibold">{{ $pv['_mapel_label'] ?? 'Jam 0' }}</div>
                                                <span class="badge bg-secondary">Jam 0</span>
                                                @endif
                                                @else
                                                <div class="fw-semibold">{{ $pvMapelLabel ?? 'Mapel' }}</div>
                                                @if(!empty($pv['_guru_nama']))
                                                <div class="text-muted small">{{ $pv['_guru_nama'] }}</div>
                                                @endif
                                                @endif
                                                <span class="badge bg-primary">PREVIEW</span>
                                            </div>

                                            @elseif ($data)
                                            {{-- DB --}}
                                            @php
                                            $jenis = $data->jenis ?? 'MAPEL';
                                            $dbMapelNama = $data->guruMapel->mataPelajaran->nama_mata_pelajaran
                                            ?? $data->guruMapel->mataPelajaran->nama
                                            ?? null;

                                            $jenisClass = match($jenis) {
                                            'UPACARA' => 'bg-upacara',
                                            'EKSKUL' => 'bg-ekskul',
                                            'KEGIATAN'=> 'bg-kegiatan',
                                            default => null,
                                            };

                                            if ($jenisClass) {
                                            $dbClass = $jenisClass;
                                            } else {
                                            $key = mb_strtolower(trim((string)$dbMapelNama));
                                            $idx = abs(crc32($key)) % 10;
                                            $dbClass = 'bg-mapel-' . $idx;
                                            }
                                            @endphp

                                            <div class="card-slot {{ $dbClass }}">
                                                @if (in_array($jenis, ['UPACARA','KEGIATAN'], true) && (int)$data->jam === 0)
                                                <div class="small fw-bold">{{ $jam0Labels[$mapHariNum[$hari]] ?? 'Kegiatan Jam 0' }}</div>
                                                <span class="badge bg-secondary">Jam 0</span>

                                                @elseif ($jenis === 'EKSKUL')
                                                <div class="small fw-bold">Ekstrakurikuler</div>
                                                <span class="badge bg-info">EKSKUL</span>
                                                <div><span class="badge bg-secondary">{{ $data->kelas->nama_kelas }}</span></div>

                                                @else
                                                <div class="small fw-bold">{{ $dbMapelNama ?? '-' }}</div>
                                                <div class="small text-muted">{{ $data->guruMapel->guru->name ?? '-' }}</div>
                                                <div><span class="badge bg-secondary">{{ $data->ruangan->nama ?? $data->kelas->nama_kelas }}</span></div>
                                                @endif
                                            </div>

                                            @else
                                            {{-- KOSONG --}}
                                            <div style="height:60px;"></div>
                                            @endif

                                            {{-- Tombol aksi --}}
                                            <div class="d-flex justify-content-center gap-1 mt-1 position-relative" style="z-index:2;">
                                                @if ($pv)
                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled title="Preview aktif — simpan atau batalkan">
                                                    Preview aktif
                                                </button>
                                                @elseif ($data)
                                                <button type="button" class="btn btn-sm btn-outline-warning btn-pilih-mapel"
                                                    data-bs-toggle="modal" data-bs-target="#modalPilihMapel"
                                                    data-id="{{ $data->id }}"
                                                    data-hari="{{ $mapHariNum[$hari] }}"
                                                    data-jam="{{ $i }}"
                                                    data-gmp="{{ $data->guru_mata_pelajaran_id }}"
                                                    data-ruangan="{{ $data->ruangan_id }}">
                                                    Edit
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-hapus-jadwal"
                                                    data-id="{{ $data->id }}"
                                                    data-mapel="{{ $data->guruMapel->mataPelajaran->nama_mata_pelajaran ?? 'mapel' }}"
                                                    data-action="{{ route('admin.penjadwalan.destroy',$data->id) }}"
                                                    data-bs-toggle="modal" data-bs-target="#modalHapusJadwal">
                                                    Hapus
                                                </button>
                                                @else
                                                <button type="button" class="btn btn-sm btn-outline-primary btn-pilih-mapel"
                                                    data-bs-toggle="modal" data-bs-target="#modalPilihMapel"
                                                    data-hari="{{ $mapHariNum[$hari] }}" data-jam="{{ $i }}">
                                                    Pilih Mapel
                                                </button>
                                                @endif
                                            </div>
                                        </td>
                                        @else
                                        <td></td>
                                        @endif
                                        @endforeach
                                </tr>
                                @endif
                                @endfor
                    </tbody>
                </table>
            </div>

            @endif

            {{-- === KEKURANGAN JAM MAPEL (inline, collapsible) === --}}
            @if($kelasTerpilih)
            @php
            // id unik per kelas biar aman kalau ada banyak card di halaman
            $rowsKekurangan = collect($kekuranganMapel ?? []);
            $collapseId = 'collapseKekurangan_'.$kelasTerpilih->id;
            @endphp


            <div class="card mt-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <strong>Kekurangan Jam Mapel — {{ $kelasTerpilih->nama_kelas }}</strong>

                    <button class="btn btn-sm btn-outline-primary"
                        id="btn-{{ $collapseId }}"
                        class="btn btn-sm btn-outline-secondary"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#{{ $collapseId }}"
                        aria-expanded="true"
                        aria-controls="{{ $collapseId }}">
                        Sembunyikan/Tampilkan
                    </button>
                </div>

                <div id="{{ $collapseId }}" class="collapse show">
                    <div class="card-body p-0">
                        @if($rowsKekurangan->isEmpty())
                        <div class="p-3 text-muted text-center">Belum ada data kebutuhan mapel untuk kelas ini.</div>
                        @else
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover align-middle mb-0">
                                <colgroup>
                                    <col style="width:35%">
                                    <col style="width:25%">
                                    <col style="width:12%">
                                    <col style="width:12%">
                                    <col style="width:16%">
                                </colgroup>
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th class="text-start">Mata Pelajaran</th>
                                        <th class="text-start">Guru</th>
                                        <th>Kebutuhan</th>
                                        <th>Terisi</th>
                                        <th>Sisa</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rowsKekurangan as $row)
                                    <tr>
                                        <td>{{ $row['mapel'] }}</td>
                                        <td>{{ $row['guru'] ?? '-' }}</td>
                                        <td class="text-center">{{ $row['butuh'] }}</td>
                                        <td class="text-center">{{ $row['terisi'] }}</td>
                                        <td class="text-center">
                                            @php $sisa = (int)($row['sisa'] ?? 0); @endphp
                                            @if($sisa > 0)
                                            <span class="badge bg-danger">Kurang {{ $sisa }}</span>
                                            @elseif($sisa < 0)
                                                <span class="badge bg-warning text-dark">Lebih {{ abs($sisa) }}</span>
                                                @else
                                                <span class="badge bg-success">Pas</span>
                                                @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center table-warning">
                                            Belum ada data kebutuhan mapel.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @endif
                    </div>
                </div>
            </div>
            @endif


            <div class="d-flex justify-content-end flex-wrap gap-2 mt-4">
                @if ($kelasTerpilih)

                {{-- 🔻 Dropdown Reset --}}
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-danger dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-undo me-1"></i> Reset
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <!-- Reset kelas terpilih -->
                        <li>
                            <button type="button" class="dropdown-item text-danger"
                                data-bs-toggle="modal" data-bs-target="#modalResetJadwal"
                                {{ $kelasTerpilih ? '' : 'disabled' }}>
                                <i class="fas fa-undo me-1"></i> Reset Jadwal (kelas ini)
                            </button>
                        </li>

                        <!-- Reset semua kelas -->
                        <li>
                            <button type="button" class="dropdown-item text-danger"
                                data-bs-toggle="modal" data-bs-target="#modalResetSemua">
                                <i class="fas fa-trash me-1"></i> Reset Semua Kelas
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- 🔻 Dropdown Tambah Massal --}}
                <div class="btn-group">
                    <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                        <i class="fas fa-plus-circle me-1"></i> Tambah
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        {{-- Jam 0 all --}}
                        <li>
                            <form method="POST" action="{{ route('admin.penjadwalan.isiJam0All') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-clock me-1"></i> Tambah Jam 0 (semua kelas)
                                </button>
                            </form>
                        </li>
                        {{-- Ekskul all --}}
                        <li>
                            <form method="POST" action="{{ route('admin.penjadwalan.isiEkskulAll') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-users me-1"></i> Tambah Ekskul (semua kelas)
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
                @endif
            </div>

    </div>
    @endsection

    @push('modals')
    @include('admin.penjadwalan.partials.modal-pilih-mapel')
    @include('admin.penjadwalan.partials.modal-hapus')
    @include('admin.penjadwalan.partials.modal-reset')
    @include('admin.penjadwalan.partials.modal-resetAll')
    @endpush

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formTambahEdit = document.getElementById('formTambahJadwal');
            const formHapus = document.getElementById('formHapusJadwal');
            const modalTitle = document.getElementById('modalTitle');
            const namaMapelHapus = document.getElementById('namaMapelHapus');

            const storeUrl = "{{ route('admin.penjadwalan.store') }}";
            const updateTpl = "{{ route('admin.penjadwalan.update', ':id') }}";


            // ===== Modal Pilih/Edit =====
            document.querySelectorAll('.btn-pilih-mapel').forEach(btn => {
                btn.addEventListener('click', function() {
                    const {
                        id: jadwalId = '',
                        hari = '',
                        jam = '',
                        gmp = '',
                        ruangan = ''
                    } = this.dataset;

                    const inJadwal = document.getElementById('inputJadwalId');
                    const inHari = document.getElementById('inputHari');
                    const inJam = document.getElementById('inputJam');
                    inJadwal && (inJadwal.value = jadwalId || '');
                    inHari && (inHari.value = hari);
                    inJam && (inJam.value = jam);

                    document.querySelectorAll('input[name="guru_mata_pelajaran_id"]').forEach(r => {
                        r.checked = (r.value === gmp);
                    });

                    const ruangSelect = document.getElementById('ruangan_id');
                    if (ruangSelect) ruangSelect.value = ruangan || '';

                    const methodOverride = document.getElementById('methodOverride');
                    if (jadwalId) {
                        formTambahEdit.action = updateTpl.replace(':id', jadwalId);
                        if (methodOverride) methodOverride.value = 'PUT';
                        modalTitle && (modalTitle.textContent = 'Edit Jadwal');
                    } else {
                        formTambahEdit.action = storeUrl;
                        if (methodOverride) methodOverride.value = 'POST';
                        modalTitle && (modalTitle.textContent = 'Tambah Jadwal');
                    }

                    applyRuanganStateFromChecked();
                });
            });

            // ===== Modal Hapus =====
            document.querySelectorAll('.btn-hapus-jadwal').forEach(button => {
                button.addEventListener('click', function() {
                    const jadwalId = this.dataset.id;
                    const namaMapel = this.dataset.mapel || 'mapel ini';
                    const action = this.dataset.action;

                    if (namaMapelHapus) namaMapelHapus.textContent = namaMapel;
                    if (formHapus) formHapus.action = action;
                });
            });

            // ===== Toggle ruangan: aktif hanya jika PRAKTIKUM =====
            function applyRuanganStateFromChecked() {
                const checked = document.querySelector('input[name="guru_mata_pelajaran_id"]:checked');
                const jenis = checked?.dataset?.jenis; // 'PRAKTIKUM' | 'TEORI'
                const ruangSelect = document.getElementById('ruangan_id');
                const ruangNote = document.getElementById('ruangNote');

                if (!ruangSelect) return;

                if (jenis === 'PRAKTIKUM') {
                    ruangSelect.disabled = false;
                    ruangSelect.required = true;
                    if (ruangNote) ruangNote.innerText = "Pilih lab untuk praktikum.";
                } else {
                    ruangSelect.disabled = true;
                    ruangSelect.required = false;
                    ruangSelect.value = "";
                    if (ruangNote) ruangNote.innerText = "Ruangan otomatis sesuai kelas (teori).";
                }
            }

            document.querySelectorAll('input[name="guru_mata_pelajaran_id"]').forEach(radio => {
                radio.addEventListener('change', applyRuanganStateFromChecked);
            });

            formTambahEdit?.addEventListener('submit', function() {
                setTimeout(() => window.location.reload(), 500);
            });
        });
    </script>
    @endpush