@extends('layouts.app')

@section('title', 'Detail Laporan Harian')

@php
    $status = $laporan->status;
    $total = count(\App\Models\LaporanHarian::BAGIAN);

    // Lama pengisian: dari simpan pertama sampai dikirim
    $lama = null;
    if ($laporan->start_proses && $laporan->end_proses) {
        $menit = (int) abs($laporan->start_proses->diffInMinutes($laporan->end_proses));
        $lama = intdiv($menit, 60) . ' jam ' . $menit % 60 . ' menit';
    }
@endphp

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card mb-3">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted">Cabang</div>
                        <strong>{{ $laporan->cabang->kode_cabang }} - {{ $laporan->cabang->nama_cabang }}</strong>
                        <div class="small text-muted">{{ $laporan->cabang->region->nama_region ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted">Tanggal laporan</div>
                        <strong>{{ $laporan->tanggal_laporan->translatedFormat('d F Y') }}</strong>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted">Status</div>
                        <span
                            class="badge text-bg-{{ $status === 'FINAL' ? 'success' : 'warning' }}">{{ $status }}</span>
                        <div class="small text-muted">{{ $laporan->jumlahLengkap() }} dari {{ $total }} bagian
                            lengkap</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted">Mulai diisi</div>
                        <strong>{{ $laporan->start_proses?->format('d M Y H:i') ?? '-' }}</strong>
                        <div class="small text-muted">oleh {{ $laporan->user->name ?? '-' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted">Dikirim</div>
                        <strong>{{ $laporan->end_proses?->format('d M Y H:i') ?? '-' }}</strong>
                        @if ($lama)
                            <div class="small text-muted">Lama pengisian: {{ $lama }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title">Kelengkapan bagian</h3>
            </div>
            <div class="card-body">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Bagian</th>
                            <th>Status</th>
                            <th>Lengkap sejak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach (\App\Models\LaporanHarian::BAGIAN as $kode => $nama)
                            @php $lengkap = $laporan->sudahLengkap($kode); @endphp
                            <tr>
                                <td>{{ $nama }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $lengkap ? 'success' : 'danger' }}">
                                        {{ $lengkap ? 'Lengkap' : 'Belum lengkap' }}
                                    </span>
                                </td>
                                <td>{{ $lengkap ? $laporan->{$kode . '_selesai_at'}->format('d M Y H:i') : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Buka Kembali: hanya untuk laporan yang sudah dikirim --}}
        @if ($laporan->end_proses)
            <div class="card mb-3 border-warning">
                <div class="card-body d-flex flex-wrap align-items-center gap-3">
                    <div>
                        <div class="fw-semibold">Laporan ini sudah dikirim dan terkunci untuk cabang.</div>
                        <div class="small text-muted">Buka kembali jika ada yang perlu diperbaiki. Laporan kembali DRAFT dan
                            cabang harus mengirim ulang.</div>
                    </div>
                    <form method="POST" action="{{ route('laporan.buka', $laporan) }}" class="ms-auto"
                        onsubmit="return confirm('Buka kembali laporan ini? Cabang akan bisa mengubahnya lagi dan laporan kembali berstatus DRAFT.')">
                        @csrf
                        <button class="btn btn-warning"><i class="bi bi-unlock"></i> Buka Kembali</button>
                    </form>
                </div>
            </div>
        @endif

        <a href="{{ route('laporan.index', ['tanggal' => $laporan->tanggal_laporan->toDateString()]) }}"
            class="btn btn-secondary mb-4">Kembali</a>
    </div>
@endsection
