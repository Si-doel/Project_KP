@extends('layouts.app')

@section('title', 'Laporan Armada')

@php
    // Nilai awal tiap isian: isian yang barusan diketik (old) dulu, lalu data yang tersimpan
    $nilai = fn($f) => old($f, $data?->{$f});

    // [nama kolom, label, jenis, keterangan (opsional), daftar kolom yang dijumlah (khusus ttl)]
    // jenis: int = angka bulat, ttl = total otomatis (tidak disimpan)
    $grup = [
        'Armada Dry' => [
            ['dry_aktif', 'Aktif', 'int'],
            ['dry_rusak', 'Rusak', 'int'],
            ['_total_dry', 'Total unit (otomatis)', 'ttl', null, ['dry_aktif', 'dry_rusak']],
        ],
        'Armada Roti' => [
            ['roti_aktif', 'Aktif', 'int'],
            ['roti_rusak', 'Rusak', 'int'],
            ['_total_roti', 'Total unit (otomatis)', 'ttl', null, ['roti_aktif', 'roti_rusak']],
        ],
        'Armada Buah' => [
            ['buah_aktif', 'Aktif', 'int'],
            ['buah_rusak', 'Rusak', 'int'],
            ['_total_buah', 'Total unit (otomatis)', 'ttl', null, ['buah_aktif', 'buah_rusak']],
        ],
        'Armada Other' => [
            ['other_aktif', 'Aktif', 'int'],
            ['other_rusak', 'Rusak', 'int'],
            ['_total_other', 'Total unit (otomatis)', 'ttl', null, ['other_aktif', 'other_rusak']],
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
                        onchange="if (this.value) window.location.href = '{{ route('laporan.armada.edit', 'TANGGAL') }}'.replace('TANGGAL', this.value)">
                </div>
            </div>
        </div>

        <div class="alert alert-info py-2">
            Isi jumlah unit armada yang aktif dan yang rusak untuk tiap jenis. Semua isian wajib berupa angka bulat 0 atau
            lebih; jika tidak ada, isi dengan <strong>0</strong>.
            Setelah tersimpan, laporan masih bisa diubah sampai dikirim.
        </div>

        <form method="POST" action="{{ route('laporan.armada.update', $tgl->toDateString()) }}" novalidate>
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
                                    $jumlah = $f[4] ?? null;
                                @endphp
                                <div class="col-md-4">
                                    @if ($jenis === 'ttl')
                                        <label class="form-label" for="{{ $nama }}">{{ $label }}</label>
                                        <input type="text" id="{{ $nama }}" class="form-control" readonly
                                            tabindex="-1" data-jumlah="{{ implode(',', $jumlah) }}">
                                    @else
                                        <label class="form-label" for="{{ $nama }}">
                                            {{ $label }} <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" min="0" step="1" inputmode="numeric"
                                            id="{{ $nama }}" name="{{ $nama }}"
                                            value="{{ $nilai($nama) }}"
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

@push('scripts')
    <script>
        // Kolom "Total (otomatis)": jumlah dari isian yang disebut di data-jumlah (hanya tampilan)
        (function() {
            document.querySelectorAll('[data-jumlah]').forEach(function(out) {
                const ids = out.dataset.jumlah.split(',');

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
            });
        })();
    </script>
@endpush
