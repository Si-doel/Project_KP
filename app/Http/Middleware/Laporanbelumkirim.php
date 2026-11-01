<?php

namespace App\Http\Middleware;

use App\Models\LaporanHarian;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Penjaga di server: laporan yang sudah dikirim (end_proses terisi) tidak boleh diubah
 * oleh cabang. Dipasang di route simpan (POST) keenam form laporan.
 */
class Laporanbelumkirim
{
    public function handle(Request $request, Closure $next): Response
    {
        $user    = $request->user();
        $tanggal = $request->route('tanggal');

        // Format tanggal yang salah dibiarkan lewat; controller yang menjawab 404
        if ($user && $user->cabang_id && is_string($tanggal) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
            $sudahDikirim = LaporanHarian::where('cabang_id', $user->cabang_id)
                ->where('tanggal_laporan', $tanggal)
                ->whereNotNull('end_proses')
                ->exists();

            if ($sudahDikirim) {
                // Kembali ke halaman form yang sama (alamat POST sama dengan alamat tampil)
                return redirect($request->url())->with(
                    'error',
                    'Laporan tanggal ini sudah dikirim dan tidak bisa diubah. Hubungi admin pusat untuk membukanya kembali.'
                );
            }
        }

        return $next($request);
    }
}
