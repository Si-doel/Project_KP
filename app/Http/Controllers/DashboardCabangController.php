<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardCabangController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $tgl = $this->tanggalAtauHariIni($request->query('tanggal'));

        // Akun yang belum terhubung ke cabang: view menampilkan peringatan
        $laporan = $user->cabang_id === null
            ? null
            : LaporanHarian::where('cabang_id', $user->cabang_id)
            ->where('tanggal_laporan', $tgl->toDateString())
            ->first();

        return view('dashboard-cabang', [
            'cabang'  => $user->cabang,
            'tgl'     => $tgl,
            'laporan' => $laporan,
        ]);
    }

    /**
     * Tanggal dari ?tanggal=Y-m-d. Kosong, salah format, tidak ada di kalender,
     * atau masa depan => pakai hari ini (bukan error, karena ini hanya filter).
     */
    private function tanggalAtauHariIni(mixed $input): Carbon
    {
        $hariIni = now()->startOfDay();

        if (
            ! is_string($input)
            || ! preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $input, $m)
            || ! checkdate((int) $m[2], (int) $m[3], (int) $m[1])
        ) {
            return $hariIni;
        }

        $tgl = Carbon::createFromFormat('!Y-m-d', $input);

        return $tgl->gt($hariIni) ? $hariIni : $tgl;
    }
}
