@extends('layouts.app')

@section('title', 'Dashboard Cabang')

@php
    // Ikon setiap bagian laporan
    $ikon = [
        'opr' => 'bi-gear-wide-connected',
        'prf' => 'bi-graph-up-arrow',
        'inv' => 'bi-box-seam',
        'kry' => 'bi-people',
        'utl' => 'bi-tools',
        'arm' => 'bi-truck',
    ];

    // Nama route setiap bagian laporan
    $rute = [
        'opr' => 'operasional',
        'prf' => 'performance',
        'inv' => 'inventory',
        'kry' => 'karyawan',
        'utl' => 'utilitas',
        'arm' => 'armada',
    ];

    $semua = \App\Models\LaporanHarian::BAGIAN;
    $total = count($semua);

    // Status dan kelengkapan laporan
    $lengkap = $laporan?->jumlahLengkap() ?? 0;
    $status = $laporan ? $laporan->status : 'BELUM DIISI';

    $warnaStatus =
        [
            'FINAL' => 'success',
            'DRAFT' => 'warning',
        ][$status] ?? 'secondary';

    $final = $status === 'FINAL';

    // Format angka KPI
    $formatAngka = function ($nilai, $desimal = 0, $akhiran = '') {
        if ($nilai === null) {
            return '-';
        }

        return number_format((float) $nilai, $desimal, ',', '.') . $akhiran;
    };

    // Enam KPI utama
    $kpiUtama = [
        [
            'nama' => 'NPB ALL',
            'kategori' => 'Operasional',
            'nilai' => $final ? $formatAngka($laporan->operasional?->npb_all) : '-',
            'ikon' => 'bi bi-box-seam',
            'warna' => 'text-bg-primary',
        ],
        [
            'nama' => 'BPB ALL',
            'kategori' => 'Operasional',
            'nilai' => $final ? $formatAngka($laporan->operasional?->bpb_all) : '-',
            'ikon' => 'bi bi-box-arrow-in-down',
            'warna' => 'text-bg-success',
        ],
        [
            'nama' => 'NRB ke Supplier',
            'kategori' => 'Inventory',
            'nilai' => $final ? $formatAngka($laporan->inventory?->nrb_ke_supplier) : '-',
            'ikon' => 'bi bi-truck',
            'warna' => 'text-bg-warning',
        ],
        [
            'nama' => 'Paket Kirim',
            'kategori' => 'Operasional',
            'nilai' => $final ? $formatAngka($laporan->operasional?->paket_kirim) : '-',
            'ikon' => 'bi bi-boxes',
            'warna' => 'text-bg-info',
        ],
        [
            'nama' => 'SL Pick Qty',
            'kategori' => 'Performance',
            'nilai' => $final ? $formatAngka($laporan->performance?->sl_pick_qty, 1, '%') : '-',
            'ikon' => 'bi bi-graph-up-arrow',
            'warna' => 'text-bg-secondary',
        ],
        [
            'nama' => 'SL Pick Item',
            'kategori' => 'Performance',
            'nilai' => $final ? $formatAngka($laporan->performance?->sl_pick_item, 1, '%') : '-',
            'ikon' => 'bi bi-bar-chart-line',
            'warna' => 'text-bg-danger',
        ],
    ];

    // Persentase kelengkapan laporan
    $persentaseLengkap = $total > 0 ? ($lengkap / $total) * 100 : 0;
@endphp

@section('content')
    <div class="container-fluid">

        {{-- Notifikasi --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        @if (!$cabang)

            <div class="alert alert-warning">
                Akun Anda belum terhubung ke cabang.
                Hubungi admin pusat.
            </div>
        @else
            {{-- Identitas Cabang dan Filter Tanggal --}}
            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap align-items-center gap-3">

                    <div>
                        <div class="fw-semibold">
                            {{ $cabang->kode_cabang }} -
                            {{ $cabang->nama_cabang }}
                        </div>

                        <div class="text-muted small">
                            {{ $cabang->region->nama_region ?? '-' }}
                        </div>
                    </div>

                    <form method="GET" class="ms-auto d-flex align-items-center gap-2">

                        <label for="tanggal" class="mb-0">
                            Tanggal akhir
                        </label>

                        <input type="date" id="tanggal" name="tanggal" class="form-control"
                            value="{{ $tgl->toDateString() }}" max="{{ now()->toDateString() }}"
                            onchange="this.form.submit()">

                        <label for="periode" class="mb-0 ms-2">
                            Periode
                        </label>

                        <select id="periode" name="periode" class="form-select" style="width: auto"
                            onchange="this.form.submit()">
                            <option value="7" {{ $periode === 7 ? 'selected' : '' }}>7 hari</option>
                            <option value="14" {{ $periode === 14 ? 'selected' : '' }}>14 hari</option>
                            <option value="30" {{ $periode === 30 ? 'selected' : '' }}>30 hari</option>
                        </select>

                    </form>

                </div>
            </div>

            {{-- Enam KPI Utama --}}
            <div class="row">

                @foreach ($kpiUtama as $kpi)
                    <div class="col-lg-4 col-md-6 col-6 mb-3">

                        <div class="small-box {{ $kpi['warna'] }}">

                            <div class="inner">

                                <h3>
                                    {{ $kpi['nilai'] }}
                                </h3>

                                <p>
                                    {{ $kpi['nama'] }}
                                </p>

                                <small>
                                    {{ $kpi['kategori'] }}
                                </small>

                            </div>

                            <i class="{{ $kpi['ikon'] }} small-box-icon"></i>

                            <div class="small-box-footer">
                                {{ $kpi['kategori'] }}
                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

            {{-- Grafik tren laporan FINAL --}}
            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Tren NPB ALL</h3>
                        </div>
                        <div class="card-body">
                            <div style="height: 280px">
                                <canvas id="grafikNpb"></canvas>
                            </div>
                            <small class="text-muted">Garis MA3 dihitung dari tiga observasi FINAL terakhir yang
                                tersedia.</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Tren BPB ALL</h3>
                        </div>
                        <div class="card-body">
                            <div style="height: 280px">
                                <canvas id="grafikBpb"></canvas>
                            </div>
                            <small class="text-muted">Garis MA3 dihitung dari tiga observasi FINAL terakhir yang
                                tersedia.</small>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Monitoring Toko</h3>
                        </div>
                        <div class="card-body">
                            <div style="height: 280px">
                                <canvas id="grafikToko"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 mb-3">
                    <div class="card h-100">
                        <div class="card-header">
                            <h3 class="card-title">Monitoring Paket</h3>
                        </div>
                        <div class="card-body">
                            <div style="height: 280px">
                                <canvas id="grafikPaket"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Progress Kelengkapan Laporan --}}
            <div class="card mb-3">

                <div class="card-body">

                    <div class="d-flex justify-content-between flex-wrap gap-2">

                        <span>
                            Kelengkapan laporan
                        </span>

                        <strong>
                            {{ $lengkap }} dari {{ $total }} bagian lengkap
                        </strong>

                    </div>

                    <div class="progress mt-2" style="height: 10px">

                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $persentaseLengkap }}%"
                            aria-valuenow="{{ $persentaseLengkap }}" aria-valuemin="0" aria-valuemax="100">
                        </div>

                    </div>

                    <div class="mt-2">

                        Status:

                        <span class="badge text-bg-{{ $warnaStatus }}">
                            {{ $status }}
                        </span>

                        @if ($laporan?->end_proses)
                            <span class="text-muted small ms-1">
                                Dikirim
                                {{ $laporan->end_proses->format('d M Y H:i') }}
                            </span>
                        @endif

                    </div>

                    {{-- Tombol Kirim Laporan --}}
                    @if ($laporan?->siapKirim())

                        <div class="alert alert-info mt-3 mb-0 d-flex align-items-center gap-3">

                            <div>
                                Semua bagian sudah lengkap.
                                Laporan siap dikirim.
                            </div>

                            @if (Route::has('laporan.kirim'))
                                <a href="{{ route('laporan.kirim', $tgl->toDateString()) }}"
                                    class="btn btn-sm btn-primary ms-auto">

                                    Kirim Laporan

                                </a>
                            @endif

                        </div>

                    @endif

                </div>

            </div>

            {{-- Daftar Bagian Laporan --}}
            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">
                        Bagian Laporan
                    </h3>

                </div>

                <ul class="list-group list-group-flush">

                    @foreach ($semua as $kode => $nama)
                        @php
                            $namaRute = 'laporan.' . $rute[$kode] . '.edit';

                            $ada = Route::has($namaRute);

                            $selesai = $laporan?->sudahLengkap($kode) ?? false;

                            $waktuSelesai = $laporan ? $laporan->{$kode . '_selesai_at'} : null;
                        @endphp

                        <li class="list-group-item d-flex align-items-center gap-3 flex-wrap">

                            {{-- Ikon --}}
                            <i class="bi {{ $ikon[$kode] }} fs-5 text-secondary"></i>

                            {{-- Nama Bagian --}}
                            <div class="fw-semibold" style="min-width: 120px">
                                {{ $nama }}
                            </div>

                            {{-- Status Bagian --}}
                            <div>

                                @if (!$ada)
                                    <span class="badge text-bg-light text-muted border">
                                        Segera tersedia
                                    </span>
                                @elseif ($selesai)
                                    <span class="badge text-bg-success">
                                        Lengkap
                                    </span>

                                    @if ($waktuSelesai)
                                        <span class="text-muted small ms-1">
                                            {{ $waktuSelesai->format('d M Y H:i') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="badge text-bg-secondary">
                                        Belum diisi
                                    </span>
                                @endif

                            </div>

                            {{-- Tombol Aksi --}}
                            <div class="ms-auto">

                                @if ($ada)
                                    <a href="{{ route($namaRute, $tgl->toDateString()) }}"
                                        class="btn btn-sm {{ $final || $selesai ? 'btn-outline-secondary' : 'btn-primary' }}">

                                        {{ $final ? 'Lihat' : ($selesai ? 'Ubah' : 'Isi') }}

                                    </a>
                                @endif

                            </div>

                        </li>
                    @endforeach

                </ul>

            </div>

        @endif

    </div>
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const dataGrafik = @json($chartData);
            const labels = dataGrafik.labels;

            const formatRupiah = (nilai) => {
                if (nilai === null || nilai === undefined) return '';
                return new Intl.NumberFormat('id-ID').format(nilai);
            };

            const opsiUmum = {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ' + formatRupiah(context.parsed.y);
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => formatRupiah(value)
                        }
                    }
                }
            };

            function buatGrafikTren(id, aktual, ma3, labelAktual) {
                const elemen = document.getElementById(id);
                if (!elemen) return;

                new Chart(elemen, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                                label: labelAktual,
                                data: aktual,
                                borderWidth: 2,
                                tension: 0.25,
                                spanGaps: false
                            },
                            {
                                label: 'Moving Average 3',
                                data: ma3,
                                borderWidth: 2,
                                borderDash: [6, 4],
                                tension: 0.25,
                                spanGaps: false
                            }
                        ]
                    },
                    options: opsiUmum
                });
            }

            function buatGrafikPerbandingan(id, seri) {
                const elemen = document.getElementById(id);
                if (!elemen) return;

                new Chart(elemen, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: seri.map(item => ({
                            label: item.label,
                            data: item.data,
                            borderWidth: 1
                        }))
                    },
                    options: opsiUmum
                });
            }

            buatGrafikTren('grafikNpb', dataGrafik.npb.aktual, dataGrafik.npb.ma3, 'NPB ALL');
            buatGrafikTren('grafikBpb', dataGrafik.bpb.aktual, dataGrafik.bpb.ma3, 'BPB ALL');

            buatGrafikPerbandingan('grafikToko', [{
                    label: 'Toko Picking',
                    data: dataGrafik.toko.picking
                },
                {
                    label: 'Toko Kirim',
                    data: dataGrafik.toko.kirim
                }
            ]);

            buatGrafikPerbandingan('grafikPaket', [{
                    label: 'Paket Terbentuk',
                    data: dataGrafik.paket.terbentuk
                },
                {
                    label: 'Paket Kirim',
                    data: dataGrafik.paket.kirim
                }
            ]);
        });
    </script>
@endpush
