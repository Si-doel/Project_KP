@extends('layouts.app')

@section('content')
    @php $isEdit = $user->exists; @endphp
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $isEdit ? 'Edit User' : 'Tambah User' }}</h3>
            </div>

            <form method="POST" action="{{ $isEdit ? route('users.update', $user) : route('users.store') }}">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" maxlength="100" value="{{ old('name', $user->name) }}"
                                class="form-control @error('name') is-invalid @enderror" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">NIK</label>
                            <input type="text" name="nik" maxlength="20" inputmode="numeric"
                                value="{{ old('nik', $user->nik) }}" class="form-control @error('nik') is-invalid @enderror"
                                required>
                            @error('nik')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" maxlength="100" value="{{ old('email', $user->email) }}"
                                class="form-control @error('email') is-invalid @enderror" required>
                            <div class="form-text">Dipakai untuk notifikasi sistem.</div>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Role</label>
                            <select name="role" id="role" class="form-select @error('role') is-invalid @enderror"
                                required>
                                @foreach (['CABANG' => 'Operator Cabang', 'ADMIN' => 'Admin Pusat'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('role', $user->role ?? 'CABANG') === $val)>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4" id="wrap-cabang">
                            <label class="form-label">Cabang</label>
                            <select name="cabang_id" class="form-select @error('cabang_id') is-invalid @enderror">
                                <option value="">-- Pilih Cabang --</option>
                                @foreach ($cabangs as $cabang)
                                    <option value="{{ $cabang->id }}" @selected(old('cabang_id', $user->cabang_id) == $cabang->id)>
                                        {{ $cabang->kode_cabang }} - {{ $cabang->nama_cabang }}
                                    </option>
                                @endforeach
                            </select>
                            @error('cabang_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status Akun</label>
                            <select name="status_akun" class="form-select @error('status_akun') is-invalid @enderror"
                                required>
                                @foreach (['AKTIF' => 'Aktif', 'NONAKTIF' => 'Nonaktif'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('status_akun', $user->status_akun ?? 'AKTIF') === $val)>{{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status_akun')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Password
                                @if ($isEdit)
                                    <small class="text-muted">(kosongkan jika tidak diganti)</small>
                                @endif
                            </label>
                            <input type="password" name="password" autocomplete="new-password"
                                class="form-control @error('password') is-invalid @enderror"
                                {{ $isEdit ? '' : 'required' }}>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" autocomplete="new-password"
                                class="form-control">
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Pilihan cabang hanya relevan untuk role CABANG
        const role = document.getElementById('role');
        const wrapCabang = document.getElementById('wrap-cabang');

        function toggleCabang() {
            wrapCabang.style.display = role.value === 'CABANG' ? '' : 'none';
        }
        role.addEventListener('change', toggleCabang);
        toggleCabang();
    </script>
@endsection
