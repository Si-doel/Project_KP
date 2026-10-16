<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegionController;
use App\Http\Controllers\CabangController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\LaporanOperasionalController;
use App\Http\Controllers\LaporanPerformanceController;
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
});


Route::middleware(['auth', 'role:CABANG'])->group(function () {
    Route::get('/dashboard-cabang', fn() => view('dashboard-cabang'))->name('dashboard.cabang');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// menu sidebar cabang

Route::get('/laporan/{tanggal}/operasional', [LaporanOperasionalController::class, 'edit'])
    ->name('laporan.operasional.edit');
Route::post('/laporan/{tanggal}/operasional', [LaporanOperasionalController::class, 'update'])
    ->name('laporan.operasional.update');

// Laporan Performance
Route::get('/laporan/{tanggal}/performance', [LaporanPerformanceController::class, 'edit'])
    ->name('laporan.performance.edit');
Route::post('/laporan/{tanggal}/performance', [LaporanPerformanceController::class, 'update'])
    ->name('laporan.performance.update');

require __DIR__ . '/auth.php';
