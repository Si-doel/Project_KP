<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\LaporanHarian;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Sisi admin: memantau kelengkapan laporan semua cabang dan membuka kembali laporan yang sudah dikirim.
 */
class LaporanAdminController extends Controller
{
    /** Jumlah cabang per halaman pada daftar kelengkapan. */
    private const PER_HALAMAN = 15;

    /** Daftar kelengkapan: semua cabang aktif pada satu tanggal. */
    public function index(Request $request)
    {
        $tgl = $this->tanggalAtauHariIni($request->query('tanggal'));

        $cabangs = Cabang::with('region')
            ->where('status', 'AKTIF')
            ->when($request->query('region_id'), fn($q, $id) => $q->where('region_id', $id))
            ->orderBy('kode_cabang')
            ->get();

        // Satu query untuk semua laporan pada tanggal itu, dikelompokkan per cabang
        $laporans = LaporanHarian::where('tanggal_laporan', $tgl->toDateString())
            ->get()
            ->keyBy('cabang_id');

        $statusDari = fn(Cabang $c) => $laporans->get($c->id)?->status ?? 'BELUM';

        $ringkas = [
            'total' => $cabangs->count(),
            'final' => $cabangs->filter(fn($c) => $statusDari($c) === 'FINAL')->count(),
            'draft' => $cabangs->filter(fn($c) => $statusDari($c) === 'DRAFT')->count(),
            'belum' => $cabangs->filter(fn($c) => $statusDari($c) === 'BELUM')->count(),
        ];

        if ($status = $request->query('status')) {
            $cabangs = $cabangs->filter(fn($c) => $statusDari($c) === $status);
        }

        // Halaman: ringkasan di atas tetap menghitung semua cabang, hanya tabel yang dipotong per halaman.
        $total   = $cabangs->count();
        $terakhir = max(1, (int) ceil($total / self::PER_HALAMAN));
        $halaman  = min(max(1, LengthAwarePaginator::resolveCurrentPage()), $terakhir);

        $baris = new LengthAwarePaginator(
            $cabangs->forPage($halaman, self::PER_HALAMAN)->values(),
            $total,
            self::PER_HALAMAN,
            $halaman,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.laporan.index', [
            'tgl'      => $tgl,
            'baris'    => $baris,
            'laporans' => $laporans,
            'ringkas'  => $ringkas,
            'regions'  => Region::orderBy('nama_region')->get(),
        ]);
    }

    /** Detail satu laporan: kelengkapan enam bagian, waktu proses, dan tombol Buka Kembali. */
    public function show(LaporanHarian $laporan)
    {
        $laporan->load('cabang.region', 'user');

        return view('admin.laporan.show', compact('laporan'));
    }

    /**
     * Buka kembali laporan yang sudah dikirim: waktu kirim (end_proses) dikosongkan,
     * laporan kembali DRAFT, dan cabang bisa mengubah keenam bagian lagi.
     */
    public function buka(LaporanHarian $laporan)
    {
        if ($laporan->end_proses === null) {
            return back()->with('error', 'Laporan ini belum dikirim, jadi tidak perlu dibuka kembali.');
        }

        $laporan->update(['end_proses' => null]);

        return back()->with('success', 'Laporan dibuka kembali. Cabang dapat mengubahnya dan mengirim ulang.');
    }

    /**
     * Tanggal dari ?tanggal=Y-m-d. Kosong, salah format, tidak ada di kalender,
     * atau masa depan => hari ini (ini hanya filter, bukan error).
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
