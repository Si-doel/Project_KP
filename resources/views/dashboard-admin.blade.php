@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')

<style>
    .dashboard-admin {
        color: #344054;
    }
    .dashboard-admin .card {
        border: 1px solid #E4E7EC;
        border-radius: 10px;
        box-shadow: 0 2px 8px rgba(16, 24, 40, .045);
        overflow: hidden;
    }
    .dashboard-admin .card-header {
        background: #FFFFFF;
        border-bottom: 1px solid #EAECF0;
        padding: 16px 20px;
    }
    .dashboard-admin .section-title {
        display: block;
        float: none;
        margin: 0;
        color: #1D2939;
        font-size: 17px;
        line-height: 1.35;
        font-weight: 650;
    }
    .dashboard-admin .section-subtitle {
        margin: 5px 0 0;
        color: #667085;
        font-size: 12px;
        line-height: 1.5;
        font-weight: 400;
    }
    .dashboard-admin .filter-label {
        display: block;
        margin-bottom: 6px;
        color: #475467;
        font-size: 12px;
        font-weight: 600;
    }
    .dashboard-admin .filter-note {
        color: #667085;
        font-size: 12px;
        line-height: 1.5;
        padding-top: 4px;
    }
    .dashboard-admin .chart-card-body {
        padding: 18px 20px;
    }
    .dashboard-admin .chart-info {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        padding: 13px 20px;
        background: #F8FAFC;
        border-top: 1px solid #EAECF0;
    }
    .dashboard-admin .chart-info-title {
        margin-bottom: 3px;
        color: #475467;
        font-size: 12px;
        line-height: 1.4;
        font-weight: 600;
    }
    .dashboard-admin .chart-info-text {
        margin: 0;
        color: #667085;
        font-size: 12px;
        line-height: 1.55;
        font-weight: 400;
    }
    .dashboard-admin .small-box .inner h3 {
        font-size: 26px;
        font-weight: 700;
    }
    .dashboard-admin .small-box .inner p {
        font-size: 13px;
        font-weight: 500;
    }
    @media (max-width: 767.98px) {
        .dashboard-admin .card-header {
            padding: 14px 16px;
        }
        .dashboard-admin .section-title {
            font-size: 16px;
        }
        .dashboard-admin .chart-card-body {
            padding: 14px 12px;
        }
        .dashboard-admin .chart-info {
            padding: 12px 16px;
        }
        .dashboard-admin .small-box .inner h3 {
            font-size: 22px;
        }
    }
</style>

<div class="container-fluid dashboard-admin">
    {{-- Filter dashboard --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard.admin') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label for="tanggal" class="filter-label">Tanggal akhir</label>
                    <input type="date" id="tanggal" name="tanggal" class="form-control"
                        value="{{ $tanggal->toDateString() }}" max="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-1">
                    <label for="periode" class="filter-label">Periode</label>
                    <select id="periode" name="periode" class="form-select">
                        @foreach ([1, 7, 14, 30] as $opsi)
                            <option value="{{ $opsi }}" @selected($periode === $opsi)>{{ $opsi }} hari</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="region_id" class="filter-label">Region</label>
                    <select id="region_id" name="region_id" class="form-select">
                        <option value="">Semua Region</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}" @selected((string) $regionId === (string) $region->id)>
                                {{ $region->nama_region }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="cabang_id" class="filter-label">Cabang</label>
                    <select id="cabang_id" name="cabang_id" class="form-select">
                        <option value="">Semua Cabang</option>
                        @foreach ($cabangsFilter as $cabang)
                            <option value="{{ $cabang->id }}" @selected((string) $cabangId === (string) $cabang->id)>
                                {{ $cabang->kode_cabang }} - {{ $cabang->nama_cabang }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-1 d-grid">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-filter"></i> Filter
                    </button>
                </div>
                <div class="col-12 filter-note">
                    NPB dan BPB menampilkan akumulasi laporan FINAL selama {{ $periode }} hari
                    ({{ $awalPeriode->format('d M Y') }} – {{ $tanggal->format('d M Y') }}).
                    NKL menggunakan snapshot laporan FINAL pada {{ $tanggal->format('d M Y') }}.
                    Grafik stockout tetap menampilkan nilai per tanggal, tanpa akumulasi antarhari.
                </div>
            </form>
        </div>
    </div>

    {{-- KPI ringkasan tanggal terpilih --}}
    <div class="row">
        @php
            $kpis = [
                ['id' => 'kpiTotal', 'label' => 'Total Cabang', 'value' => number_format($ringkas['total'], 0, ',', '.'), 'color' => 'primary', 'icon' => 'bi-building'],
                ['id' => 'kpiFinal', 'label' => 'Laporan FINAL', 'value' => number_format($ringkas['final'], 0, ',', '.'), 'color' => 'success', 'icon' => 'bi-check-circle'],
                ['id' => 'kpiDraft', 'label' => 'Laporan DRAFT', 'value' => number_format($ringkas['draft'], 0, ',', '.'), 'color' => 'warning', 'icon' => 'bi-pencil-square'],
                ['id' => 'kpiBelum', 'label' => 'Belum Diisi', 'value' => number_format($ringkas['belum'], 0, ',', '.'), 'color' => 'danger', 'icon' => 'bi-exclamation-circle'],
            ];
        @endphp
        @foreach ($kpis as $kpi)
            <div class="col-xl col-md-4 col-6 mb-3">
                <div class="small-box text-bg-{{ $kpi['color'] }} h-100">
                    <div class="inner">
                        <h3 id="{{ $kpi['id'] }}">{{ $kpi['value'] }}</h3>
                        <p>{{ $kpi['label'] }}</p>
                    </div>
                    <i class="{{ $kpi['icon'] }} small-box-icon"></i>
                </div>
            </div>
        @endforeach
    </div>

    <div class="text-end mb-3">
        <small class="text-muted">
            <i class="bi bi-arrow-repeat"></i>
            Pembaruan otomatis setiap 15 detik. Terakhir diperbarui:
            <span id="dashboardUpdatedAt">Belum diperbarui</span>
            <span id="dashboardRefreshStatus" class="ms-1"></span>
        </small>
    </div>

    {{-- Top 10 operasional --}}
    <div class="mb-2">
        <h3 class="section-title">Monitoring Operasional</h3>
        <p class="section-subtitle">Cabang dengan nilai NPB dan BPB ALL tertinggi pada periode terpilih</p>
    </div>
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="section-title">Top 10 Cabang - NPB ALL</h3>
                    <p class="section-subtitle">Akumulasi nilai NPB berdasarkan laporan FINAL</p>
                </div>
                <div class="card-body chart-card-body">
                    <div style="height:370px"><canvas id="adminTopNpb"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="section-title">Top 10 Cabang - BPB ALL</h3>
                    <p class="section-subtitle">Akumulasi nilai BPB berdasarkan laporan FINAL</p>
                </div>
                <div class="card-body chart-card-body">
                    <div style="height:370px"><canvas id="adminTopBpb"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Grafik tren NPB dan BPB dengan Moving Average 3 hari --}}
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h3 class="section-title">Tren NPB ALL dan MA3</h3>
                    <p class="section-subtitle">Perkembangan nilai NPB seluruh cabang dari hari ke hari</p>
                </div>
                <div class="card-body chart-card-body">
                    <div style="height:340px"><canvas id="adminTrendNpb"></canvas></div>
                </div>
                <div class="chart-info">
                    <i class="fas fa-info-circle text-secondary mt-1"></i>
                    <div>
                        <div class="chart-info-title">Informasi Grafik</div>
                        <p class="chart-info-text">Menampilkan total NPB harian dari cabang yang sesuai filter. Garis MA3 menunjukkan rata-rata bergerak dari tiga hari berturut-turut yang memiliki data FINAL.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mb-3">
            <div class="card shadow-sm border-0">
                <div class="card-header">
                    <h3 class="section-title">Tren BPB ALL dan MA3</h3>
                    <p class="section-subtitle">Perkembangan nilai BPB seluruh cabang dari hari ke hari</p>
                </div>
                <div class="card-body chart-card-body">
                    <div style="height:340px"><canvas id="adminTrendBpb"></canvas></div>
                </div>
                <div class="chart-info">
                    <i class="fas fa-info-circle text-secondary mt-1"></i>
                    <div>
                        <div class="chart-info-title">Informasi Grafik</div>
                        <p class="chart-info-text">Menampilkan total BPB harian dari cabang yang sesuai filter. Garis MA3 menunjukkan rata-rata bergerak dari tiga hari berturut-turut yang memiliki data FINAL.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- NKL dan Stockout: snapshot pada tanggal yang dipilih --}}
    <div class="mt-2 mb-2">
        <h3 class="section-title">Monitoring Inventory</h3>
        <p class="section-subtitle">Top 10 cabang berdasarkan kondisi NKL dan stockout pada {{ $tanggal->format('d M Y') }}</p>
    </div>
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="section-title">Top 10 NKL per Cabang</h3>
                    <p class="section-subtitle">Nilai Adjust SO pada tanggal terpilih · peringkat berdasarkan nominal absolut terbesar</p>
                </div>
                <div class="card-body chart-card-body">
                    <div style="height:370px"><canvas id="adminTopNkl"></canvas></div>
                </div>
                <div class="chart-info">
                    <i class="fas fa-info-circle text-secondary mt-1"></i>
                    <div>
                        <div class="chart-info-title">Informasi Grafik</div>
                        <p class="chart-info-text">Setiap cabang hanya ditampilkan satu kali. Nilai positif dan negatif tetap mempertahankan tandanya. Cabang tanpa laporan FINAL pada tanggal tersebut tidak ditampilkan.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="section-title">Top 10 Stockout DC</h3>
                    <p class="section-subtitle">Jumlah item kosong di DC pada {{ $tanggal->format('d M Y') }}</p>
                </div>
                <div class="card-body chart-card-body">
                    <div style="height:370px"><canvas id="adminTopStockoutDc"></canvas></div>
                </div>
                <div class="chart-info">
                    <i class="fas fa-info-circle text-secondary mt-1"></i>
                    <div>
                        <div class="chart-info-title">Informasi Grafik</div>
                        <p class="chart-info-text">Menampilkan 10 cabang dengan jumlah stockout DC tertinggi pada tanggal terpilih. Tidak ada akumulasi antarhari.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="section-title">Top 10 Stockout Toko</h3>
                    <p class="section-subtitle">Jumlah item kosong di toko pada {{ $tanggal->format('d M Y') }}</p>
                </div>
                <div class="card-body chart-card-body">
                    <div style="height:370px"><canvas id="adminTopStockoutToko"></canvas></div>
                </div>
                <div class="chart-info">
                    <i class="fas fa-info-circle text-secondary mt-1"></i>
                    <div>
                        <div class="chart-info-title">Informasi Grafik</div>
                        <p class="chart-info-text">Menampilkan 10 cabang dengan jumlah stockout toko tertinggi pada tanggal terpilih. Tidak ada akumulasi antarhari.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const top10 = @json($top10Data);
    const trend = @json($chartData);
    const formatAngka = value => new Intl.NumberFormat('id-ID').format(value ?? 0);

    if (window.ChartDataLabels) {
        Chart.register(ChartDataLabels);
    }

    function buatGrafikTop10(id, labels, values, label, palet, tipe = 'angka') {
        const canvas = document.getElementById(id);
        if (!canvas) return;

        const formatNilai = value => {
            const angka = formatAngka(value);
            return tipe === 'rupiah' ? 'Rp ' + angka : angka;
        };

        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: values,
                    backgroundColor: context => {
                        const nilai = Number(context.dataset.data[context.dataIndex] ?? 0);
                        if (id === 'adminTopNkl' && nilai < 0) return '#D95B5B';
                        return palet[context.dataIndex % palet.length];
                    },
                    borderColor: context => {
                        const nilai = Number(context.dataset.data[context.dataIndex] ?? 0);
                        if (id === 'adminTopNkl' && nilai < 0) return '#B42318';
                        return palet[context.dataIndex % palet.length];
                    },
                    borderWidth: 1,
                    borderRadius: 7,
                    barPercentage: 0.72,
                    categoryPercentage: 0.8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { right: 90, left: 8, top: 4, bottom: 4 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => label + ': ' + formatNilai(ctx.parsed.x)
                        }
                    },
                    datalabels: {
                        anchor: 'end',
                        align: context => Number(context.dataset.data[context.dataIndex]) < 0 ? 'left' : 'right',
                        offset: 4,
                        clamp: true,
                        color: '#263238',
                        font: { weight: '600', size: 10 },
                        formatter: value => formatNilai(value)
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { callback: value => formatAngka(value) },
                        grid: { color: 'rgba(120, 130, 140, 0.12)' },
                        border: { display: false }
                    },
                    y: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { autoSkip: false, color: '#59636e', font: { size: 10 } }
                    }
                }
            }
        });
    }

    function buatGrafikTrend(id, aktual, ma3, label, warnaAktual, warnaMa3) {
        const canvas = document.getElementById(id);
        if (!canvas) return;

        return new Chart(canvas, {
            type: 'line',
            data: {
                labels: trend.labels,
                datasets: [
                    {
                        label: label + ' Harian',
                        data: aktual,
                        borderColor: warnaAktual,
                        backgroundColor: warnaAktual,
                        borderWidth: 3,
                        tension: 0.25,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        pointBackgroundColor: warnaAktual,
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 1.5,
                        spanGaps: false
                    },
                    {
                        label: 'MA3',
                        data: ma3,
                        borderColor: warnaMa3,
                        backgroundColor: warnaMa3,
                        borderWidth: 3,
                        borderDash: [8, 5],
                        tension: 0.25,
                        pointRadius: 2,
                        pointHoverRadius: 6,
                        pointBackgroundColor: warnaMa3,
                        pointBorderColor: '#FFFFFF',
                        pointBorderWidth: 1.5,
                        spanGaps: false
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            pointStyle: 'line',
                            padding: 22,
                            color: '#344054',
                            font: { size: 12, weight: '600' }
                        }
                    },
                    datalabels: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': ' +
                                (ctx.parsed.y === null ? 'Tidak ada data' : formatAngka(ctx.parsed.y))
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => formatAngka(value) },
                        grid: { color: 'rgba(71, 84, 103, 0.10)' },
                        border: { display: false },
                        ticks: { color: '#667085', padding: 10 }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#667085', maxRotation: 0, autoSkip: true, maxTicksLimit: 15 }
                    }
                }
            }
        });
    }

    function buatGrafikStockoutHarian(id, values, label, warna) {
        const canvas = document.getElementById(id);
        if (!canvas) return;

        return new Chart(canvas, {
            type: 'line',
            data: {
                labels: trend.labels,
                datasets: [{
                    label: label,
                    data: values,
                    borderColor: warna,
                    backgroundColor: warna,
                    borderWidth: 3,
                    tension: 0.2,
                    fill: false,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointBackgroundColor: warna,
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 1.5,
                    spanGaps: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    datalabels: {
                        display: context => context.dataset.data[context.dataIndex] !== null,
                        align: 'top',
                        offset: 5,
                        color: '#344054',
                        font: { size: 10, weight: '600' },
                        formatter: value => formatAngka(value)
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => label + ': ' + (ctx.parsed.y === null ? 'Tidak ada data' : formatAngka(ctx.parsed.y))
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, color: '#667085' },
                        grid: { color: 'rgba(71, 84, 103, 0.10)' },
                        border: { display: false }
                    },
                    x: {
                        grid: { display: false },
                        border: { display: false },
                        ticks: { color: '#667085', maxRotation: 0, autoSkip: true, maxTicksLimit: 15 }
                    }
                }
            }
        });
    }

    const paletNpb = ['#174A7E', '#1D5D91', '#246FA3', '#2D82B5', '#3994C4', '#4AA5D0', '#5CB5D9', '#70C3DF', '#86CFE5', '#A0DCEC'];
    const paletBpb = ['#176B60', '#1B7C6C', '#218D78', '#2A9D83', '#38AD8E', '#4DBB9B', '#65C8A8', '#7DD4B6', '#98DFC5', '#B5E9D5'];
    const paletNklPositif = ['#176B60', '#1B7C6C', '#218D78', '#2A9D83', '#38AD8E', '#4DBB9B', '#65C8A8', '#7DD4B6', '#98DFC5', '#B5E9D5'];
    const paletStockoutDc = ['#B54708', '#C45A0A', '#D16C0C', '#DE7E10', '#E8901D', '#EDA13A', '#F0B254', '#F3C274', '#F6D197', '#F9E0B8'];
    const paletStockoutToko = ['#7A3E9D', '#8749AA', '#9455B7', '#A263C2', '#B072CC', '#BC82D5', '#C894DE', '#D3A6E5', '#DDB9EC', '#E8CCF2'];

    // Simpan instance Chart.js agar dataset dapat diperbarui tanpa membuat grafik baru.
    const charts = {
        npb: buatGrafikTop10('adminTopNpb', top10.npb.labels, top10.npb.values, 'Total NPB ALL', paletNpb, 'rupiah'),
        bpb: buatGrafikTop10('adminTopBpb', top10.bpb.labels, top10.bpb.values, 'Total BPB ALL', paletBpb, 'rupiah'),
        nkl: buatGrafikTop10('adminTopNkl', top10.nkl.labels, top10.nkl.values, 'Adjust SO / NKL', paletNklPositif, 'rupiah'),
        stockoutDc: buatGrafikTop10('adminTopStockoutDc', top10.stockout_dc.labels, top10.stockout_dc.values, 'Stockout DC', paletStockoutDc),
        stockoutToko: buatGrafikTop10('adminTopStockoutToko', top10.stockout_toko.labels, top10.stockout_toko.values, 'Stockout Toko', paletStockoutToko),
        trendNpb: buatGrafikTrend('adminTrendNpb', trend.npb.aktual, trend.npb.ma3, 'NPB ALL', '#0757A6', '#F08C00'),
        trendBpb: buatGrafikTrend('adminTrendBpb', trend.bpb.aktual, trend.bpb.ma3, 'BPB ALL', '#087F5B', '#D6336C')
    };

    // Mengambil data baru dengan filter yang sama seperti halaman dashboard.
    const refreshUrl = @json(route('dashboard.admin.refresh'));
    const filterForm = document.querySelector('form[action="{{ route('dashboard.admin') }}"]');
    const refreshStatus = document.getElementById('dashboardRefreshStatus');
    let refreshSedangBerjalan = false;

    async function refreshDashboard() {
        if (refreshSedangBerjalan || !filterForm) return;
        refreshSedangBerjalan = true;

        try {
            const params = new URLSearchParams(new FormData(filterForm));
            const response = await fetch(refreshUrl + '?' + params.toString(), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error('Server mengembalikan status ' + response.status);
            }

            const result = await response.json();

            // Perbarui KPI.
            document.getElementById('kpiTotal').textContent = Number(result.ringkas.total).toLocaleString('id-ID');
            document.getElementById('kpiFinal').textContent = Number(result.ringkas.final).toLocaleString('id-ID');
            document.getElementById('kpiDraft').textContent = Number(result.ringkas.draft).toLocaleString('id-ID');
            document.getElementById('kpiBelum').textContent = Number(result.ringkas.belum).toLocaleString('id-ID');

            // Perbarui grafik Top 10.
            const top10Terbaru = result.top10Data;
            const dataTop10 = [
                [charts.npb, top10Terbaru.npb],
                [charts.bpb, top10Terbaru.bpb],
                [charts.nkl, top10Terbaru.nkl],
                [charts.stockoutDc, top10Terbaru.stockout_dc],
                [charts.stockoutToko, top10Terbaru.stockout_toko]
            ];

            dataTop10.forEach(([chart, data]) => {
                if (!chart) return;
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.values;
                chart.update('none');
            });

            // Perbarui grafik tren aktual dan Moving Average 3 hari.
            const trendTerbaru = result.chartData;
            [
                [charts.trendNpb, trendTerbaru.npb],
                [charts.trendBpb, trendTerbaru.bpb]
            ].forEach(([chart, data]) => {
                if (!chart) return;
                chart.data.labels = trendTerbaru.labels;
                chart.data.datasets[0].data = data.aktual;
                chart.data.datasets[1].data = data.ma3;
                chart.update('none');
            });

            document.getElementById('dashboardUpdatedAt').textContent = result.updated_at;
            refreshStatus.textContent = '• Data terbaru';
            refreshStatus.classList.remove('text-danger');
            refreshStatus.classList.add('text-success');
        } catch (error) {
            console.error('Pembaruan Dashboard Admin gagal:', error);
            refreshStatus.textContent = '• Gagal memperbarui';
            refreshStatus.classList.remove('text-success');
            refreshStatus.classList.add('text-danger');
        } finally {
            refreshSedangBerjalan = false;
        }
    }

    // Jalankan sekali saat halaman dibuka, kemudian setiap 15 detik.
    refreshDashboard();
    setInterval(refreshDashboard, 15000);
});
</script>
@endpush
