@extends('layouts.app')

@section('title', 'Master Region')

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
                <h3 class="card-title mb-0">Daftar Region</h3>
                <a href="{{ route('regions.create') }}" class="btn btn-primary btn-sm ms-auto">Tambah Region</a>
            </div>
            <div class="card-body">
                <form method="GET" class="d-flex gap-2 mb-3">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="Cari nama region atau pejabat">
                    <button class="btn btn-outline-secondary">Cari</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nama Region</th>
                                <th>Pejabat</th>
                                <th>Jabatan</th>
                                <th class="text-center">Jumlah Cabang</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($regions as $region)
                                <tr>
                                    <td>{{ $region->nama_region }}</td>
                                    <td>{{ $region->nama_pejabat ?? '-' }}</td>
                                    <td>{{ $region->jabatan_pejabat ?? '-' }}</td>
                                    <td class="text-center">{{ $region->cabangs_count }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('regions.edit', $region) }}"
                                            class="btn btn-sm btn-warning">Edit</a>
                                        <form action="{{ route('regions.destroy', $region) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Hapus region ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada data region.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $regions->links() }}
            </div>
        </div>
    </div>
@endsection
