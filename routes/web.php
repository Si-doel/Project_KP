<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DashboardCabangController;
use App\Http\Controllers\LaporanAdminController;
use App\Http\Controllers\LaporanOperasionalController;
use App\Http\Controllers\LaporanPerformanceController;
use App\Http\Controllers\LaporanInventoryController;
use App\Http\Controllers\LaporanKaryawanController;
use App\Http\Controllers\LaporanUtilitasController;
use App\Http\Controllers\LaporanArmadaController;
use App\Http\Controllers\LaporanKirimController;


// ==================================================
// HALAMAN UTAMA
// ==================================================

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});


// ==================================================
// DASHBOARD UTAMA
// Mengarahkan user sesuai role
// ==================================================

Route::get('/dashboard', function () {
    return Auth::user()->role === 'ADMIN'
        ? redirect()->route('dashboard.admin')
        : redirect()->route('dashboard.cabang');
})->middleware('auth')->name('dashboard');


// ==================================================
// MENU KHUSUS ADMIN
// ==================================================

Route::middleware(['auth', 'role:ADMIN'])->group(function () {

    // Dashboard Admin
    Route::get('/dashboard-admin', function () {
        return view('dashboard-admin');
    })->name('dashboard.admin');

    // Master Data
    Route::resource('regions', RegionController::class)->except('show');
    Route::resource('cabangs', CabangController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');

    // Monitoring Kelengkapan Laporan
    Route::get('/admin/laporan', [LaporanAdminController::class, 'index'])
        ->name('laporan.index');

    Route::get('/admin/laporan/{laporan}', [LaporanAdminController::class, 'show'])
        ->name('laporan.show');

    // Membuka kembali laporan yang sudah dikirim
    Route::post('/admin/laporan/{laporan}/buka', [LaporanAdminController::class, 'buka'])
        ->name('laporan.buka');
});


// ==================================================
// MENU KHUSUS CABANG
// ==================================================

Route::middleware(['auth', 'role:CABANG'])->group(function () {

    // Dashboard Cabang
    Route::get('/dashboard-cabang', [DashboardCabangController::class, 'index'])
        ->name('dashboard.cabang');


    // ----------------------------------------------
    // Form Input Laporan
    // ----------------------------------------------

    Route::get('/laporan/{tanggal}/operasional', [LaporanOperasionalController::class, 'edit'])
        ->name('laporan.operasional.edit');

    Route::get('/laporan/{tanggal}/performance', [LaporanPerformanceController::class, 'edit'])
        ->name('laporan.performance.edit');

    Route::get('/laporan/{tanggal}/inventory', [LaporanInventoryController::class, 'edit'])
        ->name('laporan.inventory.edit');

    Route::get('/laporan/{tanggal}/karyawan', [LaporanKaryawanController::class, 'edit'])
        ->name('laporan.karyawan.edit');

    Route::get('/laporan/{tanggal}/utilitas', [LaporanUtilitasController::class, 'edit'])
        ->name('laporan.utilitas.edit');

    Route::get('/laporan/{tanggal}/armada', [LaporanArmadaController::class, 'edit'])
        ->name('laporan.armada.edit');


    // ----------------------------------------------
    // Proses Simpan Laporan
    // Hanya dapat dilakukan sebelum laporan dikirim
    // ----------------------------------------------

    Route::middleware('laporan.belum.kirim')->group(function () {

        Route::post('/laporan/{tanggal}/operasional', [LaporanOperasionalController::class, 'update'])
            ->name('laporan.operasional.update');

        Route::post('/laporan/{tanggal}/performance', [LaporanPerformanceController::class, 'update'])
            ->name('laporan.performance.update');

        Route::post('/laporan/{tanggal}/inventory', [LaporanInventoryController::class, 'update'])
            ->name('laporan.inventory.update');

        Route::post('/laporan/{tanggal}/karyawan', [LaporanKaryawanController::class, 'update'])
            ->name('laporan.karyawan.update');

        Route::post('/laporan/{tanggal}/utilitas', [LaporanUtilitasController::class, 'update'])
            ->name('laporan.utilitas.update');

        Route::post('/laporan/{tanggal}/armada', [LaporanArmadaController::class, 'update'])
            ->name('laporan.armada.update');
    });


    // ----------------------------------------------
    // Kirim Laporan
    // ----------------------------------------------

    Route::get('/laporan/{tanggal}/kirim', [LaporanKirimController::class, 'show'])
        ->name('laporan.kirim');

    Route::post('/laporan/{tanggal}/kirim', [LaporanKirimController::class, 'store'])
        ->name('laporan.kirim.store');
});


// ==================================================
// PROFILE USER
// ==================================================

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});


// ==================================================
// AUTHENTICATION
// ==================================================

require __DIR__ . '/auth.php';
