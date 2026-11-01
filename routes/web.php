<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\DashboardCabangController;
use App\Http\Controllers\LaporanAdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaporanOperasionalController;
use App\Http\Controllers\LaporanPerformanceController;
use App\Http\Controllers\LaporanInventoryController;
use App\Http\Controllers\LaporanKaryawanController;
use App\Http\Controllers\LaporanUtilitasController;
use App\Http\Controllers\LaporanArmadaController;
use App\Http\Controllers\LaporanKirimController;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

// Pintu masuk umum: mengarahkan ke dashboard sesuai role
Route::get('/dashboard', function () {
    return Auth::user()->role === 'ADMIN'
        ? redirect()->route('dashboard.admin')
        : redirect()->route('dashboard.cabang');
})->middleware('auth')->name('dashboard');

Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::get('/dashboard-admin', fn() => view('dashboard-admin'))->name('dashboard.admin');
});

//Menu sidebar Admin

Route::resource('regions', RegionController::class)->except('show');

Route::middleware(['auth', 'role:ADMIN'])->group(function () {
    Route::get('/dashboard-admin', fn() => view('dashboard-admin'))->name('dashboard.admin');
    Route::resource('cabangs', CabangController::class)->except('show');
    Route::resource('users', UserController::class)->except('show');
    Route::get('/admin/laporan', [LaporanAdminController::class, 'index'])->name('laporan.index');
    Route::get('/admin/laporan/{laporan}', [LaporanAdminController::class, 'show'])->name('laporan.show');
    Route::post('/admin/laporan/{laporan}/buka', [LaporanAdminController::class, 'buka'])->name('laporan.buka');
});


Route::middleware(['auth', 'role:CABANG'])->group(function () {
    Route::get('/dashboard-cabang', [DashboardCabangController::class, 'index'])
        ->name('dashboard.cabang');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// menu sidebar cabang

Route::get('/laporan/{tanggal}/operasional', [LaporanOperasionalController::class, 'edit'])
    ->name('laporan.operasional.edit');
// Route::post('/laporan/{tanggal}/operasional', [LaporanOperasionalController::class, 'update'])
//     ->name('laporan.operasional.update');

// Laporan Performance
Route::get('/laporan/{tanggal}/performance', [LaporanPerformanceController::class, 'edit'])
    ->name('laporan.performance.edit');
// Route::post('/laporan/{tanggal}/performance', [LaporanPerformanceController::class, 'update'])
//     ->name('laporan.performance.update');

// Laporan Inventory
Route::get('/laporan/{tanggal}/inventory', [LaporanInventoryController::class, 'edit'])
    ->name('laporan.inventory.edit');
// Route::post('/laporan/{tanggal}/inventory', [LaporanInventoryController::class, 'update'])
//     ->name('laporan.inventory.update');

// Laporan Karyawan
Route::get('/laporan/{tanggal}/karyawan', [LaporanKaryawanController::class, 'edit'])
    ->name('laporan.karyawan.edit');
// Route::post('/laporan/{tanggal}/karyawan', [LaporanKaryawanController::class, 'update'])
//     ->name('laporan.karyawan.update');

//laporan Utilitas
Route::get('/laporan/{tanggal}/utilitas', [LaporanUtilitasController::class, 'edit'])
    ->name('laporan.utilitas.edit');
// Route::post('/laporan/{tanggal}/utilitas', [LaporanUtilitasController::class, 'update'])
//     ->name('laporan.utilitas.update');

//laporan Armada
Route::get('/laporan/{tanggal}/armada', [LaporanArmadaController::class, 'edit'])
    ->name('laporan.armada.edit');
// Route::post('/laporan/{tanggal}/armada', [LaporanArmadaController::class, 'update'])
//     ->name('laporan.armada.update');

// Kirim laporan 
Route::get('/laporan/{tanggal}/kirim', [LaporanKirimController::class, 'show'])
    ->name('laporan.kirim');
// Route::post('/laporan/{tanggal}/kirim', [LaporanKirimController::class, 'store'])
//     ->name('laporan.kirim.store');

Route::middleware('laporan.belum.kirim')->group(function () {
    Route::post('/laporan/{tanggal}/operasional', [LaporanOperasionalController::class, 'update'])->name('laporan.operasional.update');
    Route::post('/laporan/{tanggal}/performance', [LaporanPerformanceController::class, 'update'])->name('laporan.performance.update');
    Route::post('/laporan/{tanggal}/inventory',   [LaporanInventoryController::class,   'update'])->name('laporan.inventory.update');
    Route::post('/laporan/{tanggal}/karyawan',    [LaporanKaryawanController::class,    'update'])->name('laporan.karyawan.update');
    Route::post('/laporan/{tanggal}/utilitas',    [LaporanUtilitasController::class,    'update'])->name('laporan.utilitas.update');
    Route::post('/laporan/{tanggal}/armada',      [LaporanArmadaController::class,      'update'])->name('laporan.armada.update');
});


Route::get('/laporan/{tanggal}/kirim', [LaporanKirimController::class, 'show'])
    ->name('laporan.kirim');
Route::post('/laporan/{tanggal}/kirim', [LaporanKirimController::class, 'store'])
    ->name('laporan.kirim.store');


require __DIR__ . '/auth.php';
