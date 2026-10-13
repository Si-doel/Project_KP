@extends('layouts.app')

@section('content')
    @php $isEdit = $cabang->exists; @endphp
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">{{ $isEdit ? 'Edit Cabang' : 'Tambah Cabang' }}</h3>
            </div>

            <form method="POST" action="{{ $isEdit ? route('cabangs.update', $cabang) : route('cabangs.store') }}">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Kode Cabang</label>
                            <input type="text" name="kode_cabang" maxlength="4" placeholder="G999"
                                pattern="[A-Za-z0-9]{4}" title="4 karakter huruf atau angka, contoh G999"
                                style="text-transform: uppercase" value="{{ old('kode_cabang', $cabang->kode_cabang) }}"
                                class="form-control @error('kode_cabang') is-invalid @enderror" required>
                            <div class="form-text">4 karakter huruf atau angka, contoh: <code>G999</code>.</div>
                        </div>

                        <div class="col-md-9">
                            <label class="form-label">Nama Cabang</label>
                            <input type="text" name="nama_cabang" maxlength="100"
                                value="{{ old('nama_cabang', $cabang->nama_cabang) }}"
                                class="form-control @error('nama_cabang') is-invalid @enderror" required>
                            @error('nama_cabang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label">Alamat</label>
                            <textarea name="alamat" rows="2" maxlength="255" class="form-control @error('alamat') is-invalid @enderror"
                                required>{{ old('alamat', $cabang->alamat) }}</textarea>
                            @error('alamat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Kota / Kabupaten</label>
                            <input type="text" name="kota_kabupaten" maxlength="50" placeholder="contoh: KOTA BANDUNG"
                                style="text-transform: uppercase"
                                value="{{ old('kota_kabupaten', $cabang->kota_kabupaten) }}"
                                class="form-control @error('kota_kabupaten') is-invalid @enderror" required>
                            <div class="form-text">Tulis lengkap dengan awalan, contoh: <code>KOTA BANDUNG</code> atau
                                <code>KABUPATEN BOGOR</code>.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Provinsi</label>
                            <select name="provinsi" class="form-select @error('provinsi') is-invalid @enderror" required>
                                <option value="">-- Pilih Provinsi --</option>
                                @foreach (\App\Models\Cabang::daftarProvinsi() as $prov)
                                    <option value="{{ $prov }}" @selected(old('provinsi', $cabang->provinsi) === $prov)>
                                        {{ $prov }}
                                    </option>
                                @endforeach
                            </select>
                            @error('provinsi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Jenis Cabang</label>
                            <select name="jenis_cabang" class="form-select @error('jenis_cabang') is-invalid @enderror"
                                required>
                                <option value="">-- Pilih --</option>
                                @foreach (['INDUK' => 'Induk', 'DEPO' => 'Depo', 'SEWA' => 'Sewa'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('jenis_cabang', $cabang->jenis_cabang) === $val)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jenis_cabang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Region</label>
                            <select name="region_id" class="form-select @error('region_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Region --</option>
                                @foreach ($regions as $region)
                                    <option value="{{ $region->id }}" @selected(old('region_id', $cabang->region_id) == $region->id)>
                                        {{ $region->nama_region }}
                                    </option>
                                @endforeach
                            </select>
                            @error('region_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                @foreach (['AKTIF' => 'Aktif', 'TIDAK_AKTIF' => 'Tidak Aktif', 'TUTUP' => 'Tutup'] as $val => $label)
                                    <option value="{{ $val }}" @selected(old('status', $cabang->status ?? 'AKTIF') === $val)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Latitude <small class="text-muted">(opsional)</small></label>
                            <input type="number" step="any" name="latitude"
                                value="{{ old('latitude', $cabang->latitude) }}"
                                class="form-control @error('latitude') is-invalid @enderror">
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Longitude <small class="text-muted">(opsional)</small></label>
                            <input type="number" step="any" name="longitude"
                                value="{{ old('longitude', $cabang->longitude) }}"
                                class="form-control @error('longitude') is-invalid @enderror">
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('cabangs.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
