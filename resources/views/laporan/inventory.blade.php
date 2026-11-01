@extends('layouts.app')

@section('title', 'Laporan Inventory')

@php
    // Nilai awal tiap isian: isian yang barusan diketik (old) dulu, lalu data yang tersimpan
    $nilai = fn ($f) => old($f, $data?->{$f});

    // [nama kolom, label, jenis, keterangan (opsional)]
    // jenis: int = angka bulat, rp = rupiah
    $grup = [
        'Stock Out' => [
            ['stock_out_toko', 'Toko', 'int'],
            ['stock_out_dc',   'DC',   'int'],
        ],
        'Intransit NRB Proforma ALL' => [
            ['intransit_nrb_proforma', 'Intransit NRB Proforma ALL', 'rp'],
        ],
        'Intransit NRB Fisik ALL' => [
            ['nrb_fisik_di_dc',   'Di DC',        'rp'],
            ['nrb_fisik_di_toko', 'Di Toko',      'rp'],
            ['intransit_a',       'Intransit A',  'rp'],
        ],
        'NRB ke Supplier (harian)' => [
            ['nrb_ke_supplier', 'NRB ke Supplier', 'rp'],
        ],
        'NRB Sepihak (akumulasi)' => [
            ['nrb_sepihak_sudah_diambil', 'Sudah Diambil', 'rp', 'Akumulasi, diisi apa adanya dari sumber.'],
            ['nrb_sepihak_belum_diambil', 'Belum Diambil', 'rp', 'Akumulasi, diisi apa adanya dari sumber.'],
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
                <input type="date" id="tanggal" class="form-control"
                       value="{{ $tgl->toDateString() }}" max="{{ now()->toDateString() }}"
                       onchange="if (this.value) window.location.href = '{{ route('laporan.inventory.edit', 'TANGGAL') }}'.replace('TANGGAL', this.value)">
            </div>
        </div>
    </div>

    <div class="alert alert-info py-2">
        Semua isian wajib diisi. Jika tidak ada, isi dengan <strong>0</strong>. Nilai angka boleh negatif.
        Setelah tersimpan, laporan masih bisa diubah sampai dikirim.
    </div>

    <form method="POST" action="{{ route('laporan.inventory.update', $tgl->toDateString()) }}" novalidate>
        @csrf

        @foreach ($grup as $judul => $fields)
            <div class="card mb-3">
                <div class="card-header"><h3 class="card-title">{{ $judul }}</h3></div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach ($fields as $f)
                            @php
                                [$nama, $label, $jenis] = $f;
                                $hint = $f[3] ?? null;
                            @endphp
                            <div class="col-md-4">
                                <label class="form-label" for="{{ $nama }}">
                                    {{ $label }} <span class="text-danger">*</span>
                                </label>

                                {{-- Tanpa inputmode numeric: keyboard HP perlu tanda minus --}}
                                @if ($jenis === 'rp')
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" step="1"
                                               id="{{ $nama }}" name="{{ $nama }}" value="{{ $nilai($nama) }}"
                                               class="form-control @error($nama) is-invalid @enderror">
                                    </div>
                                @else
                                    <input type="number" step="1"
                                           id="{{ $nama }}" name="{{ $nama }}" value="{{ $nilai($nama) }}"
                                           class="form-control @error($nama) is-invalid @enderror">
                                @endif

                                @if ($hint)
                                    <div class="form-text">{{ $hint }}</div>
                                @endif

                                @error($nama)
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach
                    </div>
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