@extends('layouts.app')

@section('title', 'Dashboard Cabang')

@section('content')
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <p class="mb-1">Selamat datang, <strong>{{ Auth::user()->name }}</strong>.</p>
                <p class="mb-0 text-muted">
                    Cabang: {{ Auth::user()->cabang?->kode_cabang }} - {{ Auth::user()->cabang?->nama_cabang }}
                </p>
            </div>
        </div>

        {{-- nanti: form input laporan harian dan ringkasan cabang ini --}}
    </div>
@endsection
