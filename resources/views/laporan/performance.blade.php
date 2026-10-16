@extends('layouts.app')

@section('title', 'Laporan Performance')

@php
    // Nilai awal tiap isian: isian yang barusan diketik (old) dulu, lalu data yang tersimpan
    $nilai = fn($f) => old($f, $data?->{$f});
    $waktu = fn($f) => old($f, $data?->{$f}?->format('Y-m-d\TH:i:s'));

    // Batas pemilih tanggal-jam: dari tanggal laporan sampai 2 hari sesudahnya
    $min = $tgl->copy()->startOfDay()->format('Y-m-d\TH:i:s');
    $max = $tgl->copy()->addDays(2)->endOfDay()->format('Y-m-d\TH:i:s');

    // [nama kolom, label, jenis, keterangan (opsional)]
    // jenis: int = angka bulat, rp = rupiah, pct = persen, dt = tanggal + jam,
    //        tm = durasi jam:menit:detik, ttl = total otomatis (tidak disimpan)
    $grup = [
        'Produktivitas' => [
            [
                'produktivitas_akumulasi',
                'Produktivitas (akumulasi)',
                'int',
                'Diisi apa adanya dari sumber (akumulasi).',
            ],
        ],
        'Proforma Beban (harian)' => [
            ['proforma_beban_tk_all', 'Beban TK (ALL)', 'rp'],
            ['proforma_beban_dc_perish', 'Beban DC (PERISH)', 'rp'],
        ],
        'Proforma Beban DC (DRY)' => [
            ['proforma_dry_whs', 'WHS', 'rp'],
            ['proforma_dry_issuing', 'Issuing', 'rp'],
            ['proforma_dry_expedisi', 'Expedisi', 'rp'],
            ['proforma_dry_admin', 'Admin', 'rp'],
            ['proforma_dry_lain2', 'Lain-lain', 'rp'],
            ['_ttl', 'TTL Harian (otomatis)', 'ttl'],
        ],
        'Service Level (%)' => [
            ['sl_pick_item', 'SL Pick (item)', 'pct'],
            ['sl_pick_qty', 'SL Pick (qty)', 'pct'],
            ['sl_npb_pb_item', 'SL NPB vs PB (item)', 'pct'],
            ['sl_npb_pb_qty', 'SL NPB vs PB (qty)', 'pct'],
        ],
        'Revisi Scanner' => [['revisi_scanner_pick', 'Pick', 'int'], ['revisi_scanner_scan', 'Scan', 'int']],
        'Stock Opname IC Lokasi 01' => [
            ['so_jam_upload', 'Jam Upload', 'dt'],
            ['so_jam_preadjust1', 'Jam Preadjust 1', 'dt'],
            ['so_rp_preadjust1', 'Rp Preadjust 1', 'rp'],
            ['so_jam_adjust', 'Jam Adjust', 'dt'],
            ['so_rp_adjust', 'Rp Adjust', 'rp'],
        ],
        'NKL dan Barang Rusak' => [
            ['nkl_dry_ba', 'NKL DRY BA (Nota Kurang Lebih)', 'rp', 'Positif = lebih, negatif = kurang.'],
            ['barang_rusak_pabrik_beban_toko', 'Akm. Barang Rusak Pabrik DRY (beban toko)', 'rp'],
        ],
        'Time Picking' => [['time_picking', 'Time Picking (harian)', 'tm', 'Format jam:menit:detik, contoh 00:02:30.']],
        'Barang Tertinggal' => [
            ['tertinggal_container', 'Container', 'int'],
            ['tertinggal_bronjong', 'Bronjong', 'int'],
            ['tertinggal_intransit_b', 'Intransit B', 'rp'],
        ],
    ];
@endphp

@section('content')
    <div class="container-fluid">

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Ringkasan error: semua isian yang bermasalah ditampilkan sekaligus --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-1">Laporan belum bisa disimpan, periksa isian berikut:</div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Info cabang + status bagian + pilih tanggal laporan --}}
        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <div>
                    <div class="fw-semibold">{{ $cabang->kode_cabang }} - {{ $cabang->nama_cabang }}</div>
                    <div class="text-muted small">{{ $cabang->region->nama_region ?? '-' }}</div>
                    <div class="mt-1">
                        @if ($lengkap)
                            <span class="badge text-bg-success">Lengkap</span>
                        @elseif ($data)
                            <span class="badge text-bg-warning">Belum lengkap, lengkapi lalu simpan</span>
                        @else
                            <span class="badge text-bg-secondary">Belum diisi</span>
                        @endif
                    </div>
                </div>

                <div class="ms-auto d-flex align-items-center gap-2">
                    <label for="tanggal" class="mb-0">Tanggal laporan</label>
                    {{-- Tanggal ada di URL, jadi ganti tanggal = pindah alamat --}}
                    <input type="date" id="tanggal" class="form-control" value="{{ $tgl->toDateString() }}"
                        max="{{ now()->toDateString() }}"
                        onchange="if (this.value) window.location.href = '{{ route('laporan.performance.edit', 'TANGGAL') }}'.replace('TANGGAL', this.value)">
                </div>
            </div>
        </div>

        <div class="alert alert-info py-2">
            Semua isian wajib diisi. Jika tidak ada, isi dengan <strong>0</strong>. Nilai angka boleh negatif (kecuali SL, 0
            sampai 100).
            Setelah tersimpan, laporan masih bisa diubah sampai dikirim.
        </div>

        <form method="POST" action="{{ route('laporan.performance.update', $tgl->toDateString()) }}" novalidate>
            @csrf

            @foreach ($grup as $judul => $fields)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ $judul }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($fields as $f)
                                @php
                                    [$nama, $label, $jenis] = $f;
                                    $hint = $f[3] ?? null;
                                @endphp
                                <div class="col-md-4">
                                    @if ($jenis === 'ttl')
                                        <label class="form-label" for="ttl_dry">{{ $label }}</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="text" id="ttl_dry" class="form-control" readonly
                                                tabindex="-1">
                                        </div>
                                        <div class="form-text">Jumlah dari lima isian di sebelah (tidak disimpan, dihitung
                                            otomatis).</div>
                                    @else
                                        <label class="form-label" for="{{ $nama }}">
                                            {{ $label }} <span class="text-danger">*</span>
                                        </label>

                                        @if ($jenis === 'dt')
                                            <input type="datetime-local" step="1" id="{{ $nama }}"
                                                name="{{ $nama }}" min="{{ $min }}"
                                                max="{{ $max }}" value="{{ $waktu($nama) }}"
                                                class="form-control @error($nama) is-invalid @enderror">
                                        @elseif ($jenis === 'tm')
                                            <input type="time" step="1" id="{{ $nama }}"
                                                name="{{ $nama }}" value="{{ $nilai($nama) }}"
                                                class="form-control @error($nama) is-invalid @enderror">
                                        @elseif ($jenis === 'pct')
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0" max="100"
                                                    inputmode="decimal" id="{{ $nama }}"
                                                    name="{{ $nama }}" value="{{ $nilai($nama) }}"
                                                    class="form-control @error($nama) is-invalid @enderror">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        @elseif ($jenis === 'rp')
                                            {{-- Tanpa inputmode numeric: keyboard HP perlu tanda minus --}}
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" step="1" id="{{ $nama }}"
                                                    name="{{ $nama }}" value="{{ $nilai($nama) }}"
                                                    class="form-control @error($nama) is-invalid @enderror">
                                            </div>
                                        @else
                                            <input type="number" step="1" id="{{ $nama }}"
                                                name="{{ $nama }}" value="{{ $nilai($nama) }}"
                                                class="form-control @error($nama) is-invalid @enderror">
                                        @endif

                                        @if ($hint)
                                            <div class="form-text">{{ $hint }}</div>
                                        @endif

                                        @error($nama)
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    @endif
                                </div>
                            @endforeach
                        </div>

                        @if ($judul === 'Stock Opname IC Lokasi 01')
                            <div class="form-text mt-2">Pilih tanggal dan jam. Jika proses lewat tengah malam, pilih tanggal
                                hari berikutnya.</div>
                        @endif
                    </div>
                </div>
            @endforeach

            <div class="d-flex gap-2 mb-4">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('dashboard.cabang', ['tanggal' => $tgl->toDateString()]) }}"
                    class="btn btn-secondary ms-auto">Kembali</a>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        // TTL harian DRY = jumlah lima rincian (hanya tampilan, tidak dikirim ke server)
        (function() {
            const ids = ['proforma_dry_whs', 'proforma_dry_issuing', 'proforma_dry_expedisi',
                'proforma_dry_admin', 'proforma_dry_lain2'
            ];
            const out = document.getElementById('ttl_dry');

            function hitung() {
                let total = 0;
                ids.forEach(function(id) {
                    const v = parseInt(document.getElementById(id).value, 10);
                    if (!isNaN(v)) total += v;
                });
                out.value = total.toLocaleString('id-ID');
            }

            ids.forEach(function(id) {
                document.getElementById(id).addEventListener('input', hitung);
            });
            hitung();
        })();
    </script>
@endpush
