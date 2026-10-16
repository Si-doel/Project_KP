@extends('layouts.app')

@section('title', 'Dashboard Cabang')

@php
    $user = Auth::user();
    $cabang = $user->cabang;

    // Tanggal laporan dari ?tanggal=YYYY-MM-DD.
    // Kosong, tidak valid, atau masa depan => hari ini.
    // (Sementara dihitung di sini; dipindah ke controller di Bagian 5.)
    $hariIni = now()->toDateString();
    $tgl = $hariIni;
    $input = request('tanggal');

    if (
        is_string($input) &&
        preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $input, $m) &&
        checkdate((int) $m[2], (int) $m[3], (int) $m[1]) &&
        $input <= $hariIni
    ) {
        $tgl = $input;
    }

    // Nama bagian => sudah ada formnya atau belum
    $bagian = [
        'Operasional' => true,
        'Performance' => false,
        'Inventory' => false,
        'Karyawan' => false,
        'Utilitas' => false,
        'Armada' => false,
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
                        <input type="date" id="tanggal" name="tanggal" class="form-control" value="{{ $tgl }}"
                            max="{{ $hariIni }}" onchange="this.form.submit()">
                    </form>
                </div>
            </div>

            {{-- Daftar bagian laporan --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Bagian laporan</h3>
                </div>
                <ul class="list-group list-group-flush">
                    @foreach ($bagian as $nama => $tersedia)
                        <li class="list-group-item d-flex align-items-center gap-3">
                            <div class="fw-semibold" style="min-width: 130px">{{ $nama }}</div>

                            {{-- Status per bagian (Belum diisi / Tersimpan / Selesai) ditambahkan di Bagian 5 --}}

                            <div class="ms-auto">
                                @if ($tersedia)
                                    <a href="{{ route('laporan.operasional.edit', $tgl) }}"
                                        class="btn btn-sm btn-primary">Isi</a>
                                @else
                                    <span class="badge text-bg-light text-muted border">Segera tersedia</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
