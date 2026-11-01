<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanKirimController extends Controller
{
    /** Halaman Kirim Laporan: daftar kelengkapan enam bagian + tombol Kirim. */
    public function show(string $tanggal)
    {
        $tgl  = $this->parseTanggal($tanggal);
        $user = $this->user();

        $laporan = LaporanHarian::where('cabang_id', $user->cabang_id)
            ->where('tanggal_laporan', $tgl->toDateString())
            ->first();

        return view('laporan.kirim', [
            'tgl'     => $tgl,
            'cabang'  => $user->cabang,
            'laporan' => $laporan,
        ]);
    }

    /**
     * Tombol Kirim: hanya berhasil jika keenam bagian lengkap dan laporan belum pernah dikirim.
     * Pengecekan diulang di server (di dalam transaksi), bukan hanya mengandalkan tombol.
     */
    public function store(string $tanggal)
    {
        $tgl  = $this->parseTanggal($tanggal);
        $user = $this->user();

        $pesanGagal = DB::transaction(function () use ($user, $tgl) {
            $laporan = LaporanHarian::where('cabang_id', $user->cabang_id)
                ->where('tanggal_laporan', $tgl->toDateString())
                ->lockForUpdate()
                ->first();

            if (! $laporan) {
                return 'Laporan untuk tanggal ini belum dibuat. Isi keenam bagian terlebih dahulu.';
            }

            if ($laporan->end_proses !== null) {
                return 'Laporan ini sudah dikirim sebelumnya.';
            }

            if ($laporan->jumlahLengkap() < count(LaporanHarian::BAGIAN)) {
                return 'Laporan belum bisa dikirim: masih ada bagian yang belum lengkap.';
            }

            // Waktu kirim = akhir proses. Mulai proses sudah tercatat saat simpan pertama.
            $laporan->update(['end_proses' => now()]);

            return null;
        });

        if ($pesanGagal !== null) {
            return redirect()
                ->route('laporan.kirim', $tgl->toDateString())
                ->with('error', $pesanGagal);
        }

        return redirect()
            ->route('dashboard.cabang', ['tanggal' => $tgl->toDateString()])
            ->with('success', 'Laporan tanggal ' . $tgl->format('d-m-Y') . ' berhasil dikirim.');
    }

    private function user()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        abort_if($user->cabang_id === null, 403, 'Akun belum terhubung ke cabang. Hubungi admin.');

        return $user;
    }

    /**
     * Tanggal dari URL harus berformat Y-m-d, benar-benar ada di kalender,
     * dan tidak boleh masa depan. Kalau tidak, tampilkan 404.
     */
    private function parseTanggal(string $tanggal): Carbon
    {
        abort_unless(
            preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tanggal, $m)
                && checkdate((int) $m[2], (int) $m[3], (int) $m[1]),
            404
        );

        $tgl = Carbon::createFromFormat('!Y-m-d', $tanggal);

        abort_if($tgl->gt(now()->startOfDay()), 404);

        return $tgl;
    }
}
