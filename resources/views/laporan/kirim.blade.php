@extends('layouts.app')

@section('title', 'Kirim Laporan')

@php
    // kode bagian => ikon dan nama route form (laporan.{nama}.edit)
    $ikon = [
        'opr' => 'bi-gear-wide-connected',
        'prf' => 'bi-graph-up-arrow',
        'inv' => 'bi-box-seam',
        'kry' => 'bi-people',
        'utl' => 'bi-tools',
        'arm' => 'bi-truck',
    ];
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
    $final = $laporan?->end_proses !== null;
    $siap = $laporan?->siapKirim() ?? false;
@endphp

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Info cabang + pilih tanggal laporan --}}
        <div class="card mb-3">
            <div class="card-body d-flex flex-wrap align-items-center gap-3">
                <div>
                    <div class="fw-semibold">{{ $cabang->kode_cabang }} - {{ $cabang->nama_cabang }}</div>
                    <div class="text-muted small">{{ $cabang->region->nama_region ?? '-' }}</div>
                </div>

                <div class="ms-auto d-flex align-items-center gap-2">
                    <label for="tanggal" class="mb-0">Tanggal laporan</label>
                    <input type="date" id="tanggal" class="form-control" value="{{ $tgl->toDateString() }}"
                        max="{{ now()->toDateString() }}"
                        onchange="if (this.value) window.location.href = '{{ route('laporan.kirim', 'TANGGAL') }}'.replace('TANGGAL', this.value)">
                </div>
            </div>
        </div>

        {{-- Keterangan status --}}
        @if ($final)
            <div class="alert alert-success">
                <strong>Laporan sudah dikirim</strong> pada {{ $laporan->end_proses->format('d M Y H:i') }}
                dan tidak bisa diubah. Jika ada yang perlu diperbaiki, hubungi admin pusat.
            </div>
        @elseif ($siap)
            <div class="alert alert-info">
                Semua bagian sudah lengkap. Periksa sekali lagi bila perlu, lalu kirim laporan.
            </div>
        @else
            <div class="alert alert-warning">
                Laporan belum bisa dikirim: baru <strong>{{ $lengkap }} dari {{ $total }}</strong> bagian
                lengkap.
                Lengkapi bagian yang ditandai di bawah.
            </div>
        @endif

        {{-- Kelengkapan enam bagian --}}
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Kelengkapan bagian laporan</h3>
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
                            @if ($selesai)
                                <span class="badge text-bg-success"><i class="bi bi-check-lg"></i> Lengkap</span>
                                <span
                                    class="text-muted small ms-1">{{ $laporan->{$kode . '_selesai_at'}->format('d M Y H:i') }}</span>
                            @else
                                <span class="badge text-bg-danger"><i class="bi bi-x-lg"></i> Belum lengkap</span>
                            @endif
                        </div>

                        <div class="ms-auto">
                            @if ($ada)
                                <a href="{{ route($namaRute, $tgl->toDateString()) }}"
                                    class="btn btn-sm {{ $selesai || $final ? 'btn-outline-secondary' : 'btn-primary' }}">
                                    {{ $final ? 'Lihat' : ($selesai ? 'Ubah' : 'Lengkapi') }}
                                </a>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="d-flex gap-2 mb-4">
            @unless ($final)
                <form method="POST" action="{{ route('laporan.kirim.store', $tgl->toDateString()) }}"
                    onsubmit="return confirm('Setelah dikirim, laporan tidak bisa diubah. Lanjutkan?')">
                    @csrf
                    <button type="submit" class="btn btn-success" {{ $siap ? '' : 'disabled' }}>
                        <i class="bi bi-send-check"></i> Kirim Laporan
                    </button>
                </form>
            @endunless
            <a href="{{ route('dashboard.cabang', ['tanggal' => $tgl->toDateString()]) }}"
                class="btn btn-secondary ms-auto">Kembali</a>
        </div>
    </div>
@endsection
