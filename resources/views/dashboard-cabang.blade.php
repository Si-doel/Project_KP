@extends('layouts.app')

@section('title', 'Dashboard Cabang')

@php
    // kode bagian => ikon (nama bagian diambil dari LaporanHarian::BAGIAN)
    $ikon = [
        'opr' => 'bi-gear-wide-connected',
        'prf' => 'bi-graph-up-arrow',
        'inv' => 'bi-box-seam',
        'kry' => 'bi-people',
        'utl' => 'bi-tools',
        'arm' => 'bi-truck',
    ];
    // kode singkat => nama route (laporan.{nama}.edit)
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
    $lengkap = $laporan?->jumlahLengkap() ?? 0;
    $status = $laporan ? $laporan->status : 'BELUM DIISI';
    $warna = ['FINAL' => 'success', 'DRAFT' => 'warning'][$status] ?? 'secondary';
@endphp

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if (!$cabang)
            <div class="alert alert-warning">
                Akun Anda belum terhubung ke cabang. Hubungi admin pusat.
            </div>
        @else
            {{-- Info cabang + pilih tanggal laporan --}}
            <div class="card mb-3">
                <div class="card-body d-flex flex-wrap align-items-center gap-3">
                    <div>
                        <div class="fw-semibold">{{ $cabang->kode_cabang }} - {{ $cabang->nama_cabang }}</div>
                        <div class="text-muted small">{{ $cabang->region->nama_region ?? '-' }}</div>
                    </div>

                    <form method="GET" class="ms-auto d-flex align-items-center gap-2">
                        <label for="tanggal" class="mb-0">Tanggal laporan</label>
                        <input type="date" id="tanggal" name="tanggal" class="form-control"
                            value="{{ $tgl->toDateString() }}" max="{{ now()->toDateString() }}"
                            onchange="this.form.submit()">
                    </form>
                </div>
            </div>

            {{-- Progres kelengkapan --}}
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <span>Kelengkapan laporan</span>
                        <strong>{{ $lengkap }} dari {{ $total }} bagian lengkap</strong>
                    </div>
                    <div class="progress mt-2" style="height: 10px">
                        <div class="progress-bar bg-success" style="width: {{ ($lengkap / $total) * 100 }}%"></div>
                    </div>
                    <div class="mt-2">
                        Status:
                        <span class="badge text-bg-{{ $warna }}">{{ $status }}</span>
                        @if ($laporan?->end_proses)
                            <span class="text-muted small ms-1">dikirim
                                {{ $laporan->end_proses->format('d M Y H:i') }}</span>
                        @endif
                    </div>

                    @if ($laporan?->siapKirim())
                        <div class="alert alert-info mt-3 mb-0 d-flex align-items-center gap-3">
                            <div>Semua bagian sudah lengkap. Laporan siap dikirim.</div>
                            @if (Route::has('laporan.kirim'))
                                <a href="{{ route('laporan.kirim', $tgl->toDateString()) }}"
                                    class="btn btn-sm btn-primary ms-auto">Kirim Laporan</a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Daftar bagian laporan --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bagian laporan</h3>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach ($semua as $kode => $nama)
                        @php
                            $namaRute = 'laporan.' . $rute[$kode] . '.edit';
                            $ada = Route::has($namaRute);
                            $selesai = $laporan?->sudahLengkap($kode) ?? false;
                        @endphp
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <i class="bi {{ $ikon[$kode] }} fs-5 text-secondary"></i>
                            <div class="fw-semibold" style="min-width: 120px">{{ $nama }}</div>

                            <div>
                                @if (!$ada)
                                    <span class="badge text-bg-light text-muted border">Segera tersedia</span>
                                @elseif ($selesai)
                                    <span class="badge text-bg-success">Lengkap</span>
                                    <span
                                        class="text-muted small ms-1">{{ $laporan->{$kode . '_selesai_at'}->format('d M Y H:i') }}</span>
                                @else
                                    <span class="badge text-bg-secondary">Belum diisi</span>
                                @endif
                            </div>

                            <div class="ms-auto">
                                @if ($ada)
                                    <a href="{{ route($namaRute, $tgl->toDateString()) }}"
                                        class="btn btn-sm {{ $selesai ? 'btn-outline-secondary' : 'btn-primary' }}">
                                        {{ $selesai ? 'Ubah' : 'Isi' }}
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
