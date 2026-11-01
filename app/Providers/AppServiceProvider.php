<?php

namespace App\Providers;

use App\Models\LaporanHarian;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Tautan halaman (pagination) memakai gaya Bootstrap 5, bukan Tailwind bawaan Laravel,
        // karena dashboard memakai AdminLTE (Bootstrap). Dipakai di daftar Master Cabang/User/Region.
        Paginator::useBootstrapFive();

        // Status terkunci untuk keenam form laporan: laporan yang sudah dikirim (end_proses terisi).
        // Dibagikan ke view sebagai $terkunci dan $waktuKirim, jadi view form tidak perlu diedit.
        View::composer([
            'laporan.operasional',
            'laporan.performance',
            'laporan.inventory',
            'laporan.karyawan',
            'laporan.utilitas',
            'laporan.armada',
        ], function ($view) {
            $user    = Auth::user();
            $tanggal = request()->route('tanggal');
            $laporan = null;

            if ($user && $user->cabang_id && is_string($tanggal) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
                $laporan = LaporanHarian::where('cabang_id', $user->cabang_id)
                    ->where('tanggal_laporan', $tanggal)
                    ->first();
            }

            $view->with('terkunci', $laporan?->end_proses !== null);
            $view->with('waktuKirim', $laporan?->end_proses);
        });
    }
}
