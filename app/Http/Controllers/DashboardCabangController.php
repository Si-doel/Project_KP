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

        // Tanggal yang dipilih menjadi tanggal akhir periode grafik sekaligus
        // tanggal laporan harian pada kartu dan daftar bagian laporan.
        $tgl = $this->tanggalAtauHariIni($request->query('tanggal'));

        // Pilihan periode dibatasi agar hanya menerima 7, 14, atau 30 hari.
        $periodeInput = (int) $request->query('periode', 7);
        $periode = in_array($periodeInput, [7, 14, 30], true) ? $periodeInput : 7;

        $awalPeriode = $tgl->copy()->subDays($periode - 1)->startOfDay();
        $akhirPeriode = $tgl->copy()->endOfDay();

        $cabang = $user->cabang;
        $laporan = null;
        $chartData = $this->chartKosong($awalPeriode, $periode);

        if ($user->cabang_id !== null) {
            // Laporan harian untuk KPI dan daftar enam bagian.
            $laporan = LaporanHarian::with([
                'operasional',
                'performance',
                'inventory',
                'karyawan',
                'utilitas',
                'armada',
            ])
                ->where('cabang_id', $user->cabang_id)
                ->whereDate('tanggal_laporan', $tgl->toDateString())
                ->first();

            // Grafik hanya mengambil laporan FINAL (end_proses tidak NULL).
            $laporanGrafik = LaporanHarian::with(['operasional', 'performance'])
                ->where('cabang_id', $user->cabang_id)
                ->whereNotNull('end_proses')
                ->whereBetween('tanggal_laporan', [
                    $awalPeriode->toDateString(),
                    $akhirPeriode->toDateString(),
                ])
                ->orderBy('tanggal_laporan')
                ->get();

            $chartData = $this->susunDataGrafik($laporanGrafik, $awalPeriode, $periode);
        }

        return view('dashboard-cabang', [
            'cabang' => $cabang,
            'tgl' => $tgl,
            'laporan' => $laporan,
            'periode' => $periode,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Menyusun label tanggal, nilai harian, dan MA3 untuk masing-masing indikator.
     * Tanggal tanpa laporan FINAL diberi null (bukan nol).
     */
    private function susunDataGrafik($laporanGrafik, Carbon $awalPeriode, int $periode): array
    {
        $tanggalMap = [];

        foreach ($laporanGrafik as $item) {
            $tanggalMap[$item->tanggal_laporan->toDateString()] = $item;
        }

        $labels = [];
        $npb = [];
        $bpb = [];
        $tokoPicking = [];
        $tokoKirim = [];
        $paketTerbentuk = [];
        $paketKirim = [];

        for ($i = 0; $i < $periode; $i++) {
            $tanggal = $awalPeriode->copy()->addDays($i);
            $key = $tanggal->toDateString();
            $item = $tanggalMap[$key] ?? null;

            $labels[] = $tanggal->format('d/m');
            $npb[] = $this->angkaAtauNull($item?->operasional?->npb_all);
            $bpb[] = $this->angkaAtauNull($item?->operasional?->bpb_all);
            $tokoPicking[] = $this->angkaAtauNull($item?->operasional?->toko_picking);
            $tokoKirim[] = $this->angkaAtauNull($item?->operasional?->toko_kirim);
            $paketTerbentuk[] = $this->angkaAtauNull($item?->operasional?->paket_terbentuk);
            $paketKirim[] = $this->angkaAtauNull($item?->operasional?->paket_kirim);
        }

        return [
            'labels' => $labels,
            'npb' => [
                'aktual' => $npb,
                'ma3' => $this->movingAverageTiga($npb),
            ],
            'bpb' => [
                'aktual' => $bpb,
                'ma3' => $this->movingAverageTiga($bpb),
            ],
            'toko' => [
                'picking' => $tokoPicking,
                'kirim' => $tokoKirim,
            ],
            'paket' => [
                'terbentuk' => $paketTerbentuk,
                'kirim' => $paketKirim,
            ],
        ];
    }

    /** Mengubah nilai numerik menjadi angka; NULL dan string kosong tetap NULL. */
    private function angkaAtauNull(mixed $nilai): int|float|null
    {
        if ($nilai === null || $nilai === '') {
            return null;
        }

        return is_numeric($nilai) ? (float) $nilai : null;
    }

    /** MA3 dihitung dari tiga observasi FINAL valid terakhir, melewati tanggal kosong. */
    private function movingAverageTiga(array $nilaiHarian): array
    {
        $hasil = [];
        $observasi = [];

        foreach ($nilaiHarian as $nilai) {
            if ($nilai !== null) {
                $observasi[] = (float) $nilai;
                if (count($observasi) > 3) {
                    array_shift($observasi);
                }
            }

            $hasil[] = count($observasi) === 3
                ? array_sum($observasi) / 3
                : null;
        }

        return $hasil;
    }

    /** Data grafik kosong untuk akun yang belum terhubung ke cabang. */
    private function chartKosong(Carbon $awalPeriode, int $periode): array
    {
        $labels = [];

        for ($i = 0; $i < $periode; $i++) {
            $labels[] = $awalPeriode->copy()->addDays($i)->format('d/m');
        }

        $kosong = array_fill(0, $periode, null);
        $nol = array_fill(0, $periode, null);

        return [
            'labels' => $labels,
            'npb' => ['aktual' => $kosong, 'ma3' => $nol],
            'bpb' => ['aktual' => $kosong, 'ma3' => $nol],
            'toko' => ['picking' => $kosong, 'kirim' => $nol],
            'paket' => ['terbentuk' => $kosong, 'kirim' => $nol],
        ];
    }

    /** Validasi tanggal dari query string; tanggal masa depan dikembalikan ke hari ini. */
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
