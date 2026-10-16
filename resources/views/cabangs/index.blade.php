@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0">Master Cabang</h3>
                <a href="{{ route('cabangs.create') }}" class="btn btn-primary btn-sm ms-auto">Tambah Cabang</a>
            </div>
            <div class="card-body">
                <form method="GET" class="d-flex gap-2 mb-3">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="Cari kode atau nama cabang">
                    <button class="btn btn-outline-secondary">Cari</button>
                </form>

                @php
                    $sortLink = function ($column) {
                        $direction =
                            request('sort') === $column && request('direction', 'asc') === 'asc' ? 'desc' : 'asc';

                        return request()->fullUrlWithQuery([
                            'sort' => $column,
                            'direction' => $direction,
                        ]);
                    };

                    $sortIcon = function ($column) {
                        if (request('sort') !== $column) {
                            return '↕';
                        }

                        return request('direction', 'asc') === 'asc' ? '↑' : '↓';
                    };
                @endphp

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Cabang</th>
                                <th>Kota / Provinsi</th>
                                <th>Jenis</th>
                                <th>Region</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cabangs as $cabang)
                                @php
                                    $badge =
                                        ['AKTIF' => 'success', 'TIDAK_AKTIF' => 'warning', 'TUTUP' => 'danger'][
                                            $cabang->status
                                        ] ?? 'secondary';
                                @endphp
                                <tr>
                                    <td>{{ $cabang->kode_cabang }}</td>
                                    <td>{{ $cabang->nama_cabang }}</td>
                                    <td>{{ $cabang->kota_kabupaten }}, {{ $cabang->provinsi }}</td>
                                    <td>{{ $cabang->jenis_cabang }}</td>
                                    <td>{{ $cabang->region->nama_region }}</td>
                                    <td><span
                                            class="badge text-bg-{{ $badge }}">{{ str_replace('_', ' ', $cabang->status) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('cabangs.edit', $cabang) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('cabangs.destroy', $cabang) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Hapus cabang ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data cabang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $cabangs->links() }}
            </div>
        </div>
    </div>
@endsection
