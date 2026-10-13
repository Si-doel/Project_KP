@extends('layouts.app')

@php $isEdit = $region->exists; @endphp

@section('title', $isEdit ? 'Edit Region' : 'Tambah Region')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <form method="POST" action="{{ $isEdit ? route('regions.update', $region) : route('regions.store') }}">
                @csrf
                @if ($isEdit)
                    @method('PUT')
                @endif

                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Nama Region</label>
                            <input type="text" name="nama_region" maxlength="6"
                                value="{{ old('nama_region', $region->nama_region) }}" placeholder="REG-01"
                                pattern="REG-[0-9]{2}" title="Format: REG- diikuti 2 angka, contoh REG-01"
                                oninput="this.value = this.value.toUpperCase().replace(/\s/g, '')"
                                class="form-control @error('nama_region') is-invalid @enderror" required autofocus>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nama Pejabat <small class="text-muted">(opsional)</small></label>
                            <input type="text" name="nama_pejabat" maxlength="100"
                                value="{{ old('nama_pejabat', $region->nama_pejabat) }}"
                                class="form-control @error('nama_pejabat') is-invalid @enderror">
                            @error('nama_pejabat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Jabatan <small class="text-muted">(opsional)</small></label>
                            <select name="jabatan_pejabat"
                                class="form-select @error('jabatan_pejabat') is-invalid @enderror">
                                <option value="">-- Pilih Jabatan --</option>
                                @foreach (\App\Models\Region::JABATAN as $jabatan)
                                    <option value="{{ $jabatan }}" @selected(old('jabatan_pejabat', $region->jabatan_pejabat) === $jabatan)>
                                        {{ $jabatan }}
                                    </option>
                                @endforeach
                            </select>
                            @error('jabatan_pejabat')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer d-flex gap-2">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="{{ route('regions.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>
        </div>
    </div>
@endsection
