@extends('layouts.app')

@section('title', 'Kelengkapan Laporan')

@php
    $warna = ['FINAL' => 'success', 'DRAFT' => 'warning', 'BELUM' => 'secondary'];
    $label = ['FINAL' => 'FINAL', 'DRAFT' => 'DRAFT', 'BELUM' => 'BELUM MULAI'];
    $total = count(\App\Models\LaporanHarian::BAGIAN);
@endphp

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Filter --}}
        <form method="GET" class="card card-body mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label mb-1">Tanggal laporan</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $tgl->toDateString() }}"
                        max="{{ now()->toDateString() }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Region</label>
                    <select name="region_id" class="form-select">
                        <option value="">Semua region</option>
                        @foreach ($regions as $region)
                            <option value="{{ $region->id }}" @selected(request('region_id') == $region->id)>
                                {{ $region->nama_region }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label mb-1">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Semua status</option>
                        <option value="BELUM" @selected(request('status') === 'BELUM')>Belum mulai</option>
                        <option value="DRAFT" @selected(request('status') === 'DRAFT')>DRAFT</option>
                        <option value="FINAL" @selected(request('status') === 'FINAL')>FINAL</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button class="btn btn-primary">Tampilkan</button>
                    <a href="{{ route('laporan.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </div>
        </form>

        {{-- Ringkasan --}}
        <div class="row">
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-primary">
                    <div class="inner">
                        <h3>{{ $ringkas['total'] }}</h3>
                        <p>Cabang aktif</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-success">
                    <div class="inner">
                        <h3>{{ $ringkas['final'] }}</h3>
                        <p>FINAL (sudah dikirim)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-warning">
                    <div class="inner">
                        <h3>{{ $ringkas['draft'] }}</h3>
                        <p>DRAFT (belum dikirim)</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3 col-6">
                <div class="small-box text-bg-danger">
                    <div class="inner">
                        <h3>{{ $ringkas['belum'] }}</h3>
                        <p>Belum mulai</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Laporan tanggal {{ $tgl->translatedFormat('d F Y') }}</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Cabang</th>
                                <th>Region</th>
                                <th>Bagian lengkap</th>
                                <th>Status</th>
                                <th>Mulai diisi</th>
                                <th>Dikirim</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($baris as $cabang)
                                @php
                                    $l = $laporans->get($cabang->id);
                                    $st = $l ? $l->status : 'BELUM';
                                @endphp
                                <tr>
                                    <td>{{ $cabang->kode_cabang }}</td>
                                    <td>{{ $cabang->nama_cabang }}</td>
                                    <td>{{ $cabang->region->nama_region ?? '-' }}</td>
                                    <td>
                                        @foreach (\App\Models\LaporanHarian::BAGIAN as $kode => $nama)
                                            <span
                                                class="badge text-bg-{{ $l && $l->sudahLengkap($kode) ? 'success' : 'secondary' }}"
                                                title="{{ $nama }}">{{ strtoupper($kode) }}</span>
                                        @endforeach
                                        <span
                                            class="text-muted small ms-1">{{ $l?->jumlahLengkap() ?? 0 }}/{{ $total }}</span>
                                    </td>
                                    <td>
                                        <span class="badge text-bg-{{ $warna[$st] }}">{{ $label[$st] }}</span>
                                        @if ($l && $l->siapKirim())
                                            <div class="small text-muted">lengkap, belum dikirim</div>
                                        @endif
                                    </td>
                                    <td>{{ $l?->start_proses?->format('H:i') ?? '-' }}</td>
                                    <td>{{ $l?->end_proses?->format('H:i') ?? '-' }}</td>
                                    <td class="text-end">
                                        @if ($l)
                                            <a href="{{ route('laporan.show', $l) }}"
                                                class="btn btn-sm btn-outline-primary">Detail</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">Tidak ada data untuk filter ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-3">
                    <div class="text-muted small">
                        Menampilkan {{ $baris->firstItem() ?? 0 }} - {{ $baris->lastItem() ?? 0 }}
                        dari {{ $baris->total() }} cabang
                    </div>
                    {{ $baris->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
