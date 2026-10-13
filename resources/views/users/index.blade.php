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
                <h3 class="card-title mb-0">Master User</h3>
                <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm ms-auto">Tambah User</a>
            </div>
            <div class="card-body">
                <form method="GET" class="d-flex gap-2 mb-3">
                    <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                        placeholder="Cari nama atau email">
                    <button class="btn btn-outline-secondary">Cari</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Cabang</th>
                                <th>Status</th>
                                <th>Login Terakhir</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($users as $user)
                                <tr>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->role }}</td>
                                    <td>{{ $user->cabang?->nama_cabang ?? '-' }}</td>
                                    <td>
                                        <span
                                            class="badge text-bg-{{ $user->status_akun === 'AKTIF' ? 'success' : 'secondary' }}">
                                            {{ $user->status_akun }}
                                        </span>
                                    </td>
                                    <td>{{ $user->last_login_at?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-warning">Edit</a>
                                        @if ($user->id !== auth()->id())
                                            <form action="{{ route('users.destroy', $user) }}" method="POST"
                                                class="d-inline" onsubmit="return confirm('Hapus akun ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-danger">Hapus</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data user.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{ $users->links() }}
            </div>
        </div>
    </div>
@endsection
