@extends('layouts.app')

@section('title', 'Laporan Operasional')

@php
    // Nilai awal tiap isian: isian yang barusan diketik (old) dulu, lalu data yang tersimpan
    $nilai = fn($f) => old($f, $data?->{$f});
    $waktu = fn($f) => old($f, $data?->{$f}?->format('Y-m-d\TH:i:s'));

    // Batas pemilih tanggal-jam: dari tanggal laporan sampai 2 hari sesudahnya
    // (proses bisa selesai lewat tengah malam)
    $min = $tgl->copy()->startOfDay()->format('Y-m-d\TH:i:s');
    $max = $tgl->copy()->addDays(2)->endOfDay()->format('Y-m-d\TH:i:s');

    // [nama kolom, label, jenis]
    // jenis: int = jumlah, rp = rupiah, dt = tanggal + jam
    $grup = [
        'Toko' => [
            ['jumlah_toko', 'Jumlah Toko', 'int'],
            ['toko_picking', 'Jumlah Toko Picking', 'int'],
            ['toko_kirim', 'Jumlah Toko Kirim', 'int'],
        ],
        'NPB dan BPB (harian)' => [
            ['npb_all', 'NPB ALL', 'rp'],
            ['npb_dry', 'NPB DRY', 'rp'],
            ['bpb_all', 'BPB ALL', 'rp'],
        ],
        'Paket Kiriman (DRY)' => [['paket_terbentuk', 'Terbentuk', 'int'], ['paket_kirim', 'Kirim', 'int']],
        'Supplier' => [['supplier_daftar', 'Daftar', 'int'], ['supplier_pending', 'Pending', 'int']],
        'Waktu Selesai Operasional' => [
            ['waktu_selesai_rcv', 'Rcv', 'dt'],
            ['waktu_selesai_picking', 'Picking', 'dt'],
            ['waktu_selesai_loading', 'Loading', 'dt'],
            ['waktu_selesai_del', 'Del', 'dt'],
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
                            {{-- Sisa data lama yang tersimpan sebelum isian dibuat wajib --}}
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
                        onchange="if (this.value) window.location.href = '{{ route('laporan.operasional.edit', 'TANGGAL') }}'.replace('TANGGAL', this.value)">
                </div>
            </div>
        </div>

        <div class="alert alert-info py-2">
            Semua isian wajib diisi. Jika tidak ada, isi dengan <strong>0</strong>. Setelah tersimpan, laporan masih bisa
            diubah sampai dikirim.
        </div>

        <form method="POST" action="{{ route('laporan.operasional.update', $tgl->toDateString()) }}" novalidate>
            @csrf

            @foreach ($grup as $judul => $fields)
                <div class="card mb-3">
                    <div class="card-header">
                        <h3 class="card-title">{{ $judul }}</h3>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($fields as [$nama, $label, $jenis])
                                <div class="col-md-4">
                                    <label class="form-label" for="{{ $nama }}">
                                        {{ $label }} <span class="text-danger">*</span>
                                    </label>

                                    @if ($jenis === 'dt')
                                        <input type="datetime-local" step="1" id="{{ $nama }}"
                                            name="{{ $nama }}" min="{{ $min }}"
                                            max="{{ $max }}" value="{{ $waktu($nama) }}"
                                            class="form-control @error($nama) is-invalid @enderror">
                                    @elseif ($jenis === 'rp')
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input type="number" min="0" step="1" inputmode="numeric"
                                                id="{{ $nama }}" name="{{ $nama }}"
                                                value="{{ $nilai($nama) }}"
                                                class="form-control @error($nama) is-invalid @enderror">
                                        </div>
                                    @else
                                        <input type="number" min="0" step="1" inputmode="numeric"
                                            id="{{ $nama }}" name="{{ $nama }}"
                                            value="{{ $nilai($nama) }}"
                                            class="form-control @error($nama) is-invalid @enderror">
                                    @endif

                                    @error($nama)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>

                        @if ($judul === 'Waktu Selesai Operasional')
                            <div class="form-text mt-2">Pilih tanggal dan jam. Jika proses selesai lewat tengah malam, pilih
                                tanggal hari berikutnya.</div>
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
