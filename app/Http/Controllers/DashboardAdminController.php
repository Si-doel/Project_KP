<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\LaporanHarian;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DashboardAdminController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'periode' => ['nullable', 'integer', Rule::in([1, 7, 14, 30])],
            'region_id' => ['nullable', 'integer', Rule::exists('regions', 'id')],
            'cabang_id' => ['nullable', 'integer', Rule::exists('cabangs', 'id')],
        ]);

        $tanggal = $this->tanggalAtauHariIni($filter['tanggal'] ?? null);
        $periode = (int) ($filter['periode'] ?? 7);
        $awalPeriode = $tanggal->copy()->subDays($periode - 1)->startOfDay();
        $akhirPeriode = $tanggal->copy()->endOfDay();

        $regionId = $filter['region_id'] ?? null;
        $cabangId = $filter['cabang_id'] ?? null;

        $cabangs = Cabang::with('region')
            ->where('status', 'AKTIF')
            ->when($regionId, fn ($q) => $q->where('region_id', $regionId))
            ->when($cabangId, fn ($q) => $q->whereKey($cabangId))
            ->orderBy('kode_cabang')
            ->get();

        // KPI status laporan dihitung untuk tanggal akhir yang dipilih.
        $laporanHariIni = $cabangs->isEmpty()
            ? collect()
            : LaporanHarian::whereDate('tanggal_laporan', $tanggal->toDateString())
                ->whereIn('cabang_id', $cabangs->pluck('id'))
                ->get()
                ->keyBy('cabang_id');

        $statusCabang = $cabangs->map(function ($cabang) use ($laporanHariIni) {
            $laporan = $laporanHariIni->get($cabang->id);
            return $laporan?->status ?? 'BELUM';
        });

        $ringkas = [
            'total' => $cabangs->count(),
            'final' => $statusCabang->filter(fn ($status) => $status === 'FINAL')->count(),
            'draft' => $statusCabang->filter(fn ($status) => $status === 'DRAFT')->count(),
            'belum' => $statusCabang->filter(fn ($status) => $status === 'BELUM')->count(),
        ];

        // Data grafik hanya menggunakan laporan FINAL dalam rentang tanggal.
        $laporanPeriode = $cabangs->isEmpty()
            ? collect()
            : LaporanHarian::with(['operasional', 'performance', 'inventory'])
                ->whereIn('cabang_id', $cabangs->pluck('id'))
                ->whereNotNull('end_proses')
                ->whereBetween('tanggal_laporan', [
                    $awalPeriode->toDateString(),
                    $akhirPeriode->toDateString(),
                ])
                ->orderBy('tanggal_laporan')
                ->get();

        // NKL adalah snapshot pada tanggal akhir yang dipilih, bukan akumulasi periode.
        $laporanTanggalTerpilih = $cabangs->isEmpty()
            ? collect()
            : LaporanHarian::with(['performance', 'inventory'])
                ->whereIn('cabang_id', $cabangs->pluck('id'))
                ->whereDate('tanggal_laporan', $tanggal->toDateString())
                ->whereNotNull('end_proses')
                ->get();

        $top10Data = $this->susunTop10($laporanPeriode, $laporanTanggalTerpilih, $cabangs);
        $chartData = $this->susunDataTrend($laporanPeriode, $awalPeriode, $periode);

        return view('dashboard-admin', [
            'tanggal' => $tanggal,
            'periode' => $periode,
            'awalPeriode' => $awalPeriode,
            'regions' => Region::orderBy('nama_region')->get(),
            'cabangsFilter' => Cabang::where('status', 'AKTIF')
                ->when($regionId, fn ($q) => $q->where('region_id', $regionId))
                ->orderBy('kode_cabang')
                ->get(),
            'regionId' => $regionId,
            'cabangId' => $cabangId,
            'ringkas' => $ringkas,
            'top10Data' => $top10Data,
            'chartData' => $chartData,
        ]);
    }

    /**
     * Mengambil data terbaru Dashboard Admin dalam format JSON.
     * Filter tanggal, periode, region, dan cabang mengikuti halaman dashboard.
     */
    public function refresh(Request $request)
    {
        $filter = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
            'periode' => ['nullable', 'integer', Rule::in([1, 7, 14, 30])],
            'region_id' => ['nullable', 'integer', Rule::exists('regions', 'id')],
            'cabang_id' => ['nullable', 'integer', Rule::exists('cabangs', 'id')],
        ]);

        $tanggal = $this->tanggalAtauHariIni($filter['tanggal'] ?? null);
        $periode = (int) ($filter['periode'] ?? 7);
        $awalPeriode = $tanggal->copy()->subDays($periode - 1)->startOfDay();
        $akhirPeriode = $tanggal->copy()->endOfDay();
        $regionId = $filter['region_id'] ?? null;
        $cabangId = $filter['cabang_id'] ?? null;

        $cabangs = Cabang::where('status', 'AKTIF')
            ->when($regionId, fn ($q) => $q->where('region_id', $regionId))
            ->when($cabangId, fn ($q) => $q->whereKey($cabangId))
            ->orderBy('kode_cabang')
            ->get();

        // Ringkasan status untuk tanggal akhir yang dipilih.
        $laporanHariIni = $cabangs->isEmpty()
            ? collect()
            : LaporanHarian::whereDate('tanggal_laporan', $tanggal->toDateString())
                ->whereIn('cabang_id', $cabangs->pluck('id'))
                ->get()
                ->keyBy('cabang_id');

        $statusCabang = $cabangs->map(
            fn ($cabang) => $laporanHariIni->get($cabang->id)?->status ?? 'BELUM'
        );

        $ringkas = [
            'total' => $cabangs->count(),
            'final' => $statusCabang->filter(fn ($status) => $status === 'FINAL')->count(),
            'draft' => $statusCabang->filter(fn ($status) => $status === 'DRAFT')->count(),
            'belum' => $statusCabang->filter(fn ($status) => $status === 'BELUM')->count(),
        ];

        // Data grafik menggunakan laporan FINAL dalam rentang periode.
        $laporanPeriode = $cabangs->isEmpty()
            ? collect()
            : LaporanHarian::with(['operasional', 'performance', 'inventory'])
                ->whereIn('cabang_id', $cabangs->pluck('id'))
                ->whereNotNull('end_proses')
                ->whereBetween('tanggal_laporan', [
                    $awalPeriode->toDateString(),
                    $akhirPeriode->toDateString(),
                ])
                ->orderBy('tanggal_laporan')
                ->get();

        // NKL dan Stockout merupakan snapshot pada tanggal akhir terpilih.
        $laporanTanggalTerpilih = $cabangs->isEmpty()
            ? collect()
            : LaporanHarian::with(['performance', 'inventory'])
                ->whereIn('cabang_id', $cabangs->pluck('id'))
                ->whereDate('tanggal_laporan', $tanggal->toDateString())
                ->whereNotNull('end_proses')
                ->get();

        $top10Data = $this->susunTop10($laporanPeriode, $laporanTanggalTerpilih, $cabangs);
        $chartData = $this->susunDataTrend($laporanPeriode, $awalPeriode, $periode);

        return response()->json([
            'ringkas' => $ringkas,
            'top10Data' => $top10Data,
            'chartData' => $chartData,
            'updated_at' => now()->format('d-m-Y H:i:s'),
        ]);
    }

    private function susunTop10($laporanPeriode, $laporanTanggalTerpilih, $cabangs): array
    {
        $namaCabang = $cabangs->keyBy('id');

        // NPB dan BPB tetap diakumulasikan selama periode yang dipilih.
        $rekapPeriode = $laporanPeriode->groupBy('cabang_id')->map(function ($items, $cabangId) use ($namaCabang) {
            $cabang = $namaCabang->get($cabangId);
            if (!$cabang) {
                return null;
            }

            $nilaiNpb = $items->map(fn ($row) => $row->operasional?->npb_all)->filter(fn ($v) => $v !== null);
            $nilaiBpb = $items->map(fn ($row) => $row->operasional?->bpb_all)->filter(fn ($v) => $v !== null);

            return [
                'label' => $cabang->kode_cabang . ' - ' . $cabang->nama_cabang,
                'npb' => (float) $nilaiNpb->sum(),
                'bpb' => (float) $nilaiBpb->sum(),
            ];
        })->filter()->values();

        $buatDatasetPeriode = function (string $key) use ($rekapPeriode) {
            $hasil = $rekapPeriode->sortByDesc($key)->take(10)->values();
            return ['labels' => $hasil->pluck('label')->all(), 'values' => $hasil->pluck($key)->all()];
        };

        // NKL dan Stockout adalah snapshot pada tanggal akhir yang dipilih.
        // Setiap cabang hanya menghasilkan satu nilai untuk masing-masing indikator.
        $rekapSnapshot = $laporanTanggalTerpilih->groupBy('cabang_id')->map(function ($items, $cabangId) use ($namaCabang) {
            $cabang = $namaCabang->get($cabangId);
            if (!$cabang) {
                return null;
            }

            // NKL pada tanggal terpilih.
            $nilaiNkl = $items
                ->map(fn ($row) => $row->performance?->so_rp_adjust)
                ->filter(fn ($value) => $value !== null);

            // Stockout DC pada tanggal terpilih.
            $nilaiStockoutDc = $items
                ->map(fn ($row) => $row->inventory?->stock_out_dc)
                ->filter(fn ($value) => $value !== null);

            // Stockout Toko pada tanggal terpilih.
            $nilaiStockoutToko = $items
                ->map(fn ($row) => $row->inventory?->stock_out_toko)
                ->filter(fn ($value) => $value !== null);

            return [
                'label' => $cabang->kode_cabang . ' - ' . $cabang->nama_cabang,
                'nkl' => $nilaiNkl->isEmpty() ? null : (float) $nilaiNkl->sum(),
                'stockout_dc' => $nilaiStockoutDc->isEmpty() ? null : (float) $nilaiStockoutDc->sum(),
                'stockout_toko' => $nilaiStockoutToko->isEmpty() ? null : (float) $nilaiStockoutToko->sum(),
            ];
        })->filter()->values();

        $buatDatasetSnapshot = function (string $key, bool $urutAbsolut = false) use ($rekapSnapshot) {
            $data = $rekapSnapshot->filter(fn ($item) => $item[$key] !== null);

            $hasil = $urutAbsolut
                ? $data->sortBy(fn ($item) => abs($item[$key]))->reverse()
                : $data->sortByDesc($key);

            $hasil = $hasil->take(10)->values();

            return [
                'labels' => $hasil->pluck('label')->all(),
                'values' => $hasil->pluck($key)->all(),
            ];
        };

        return [
            'npb' => $buatDatasetPeriode('npb'),
            'bpb' => $buatDatasetPeriode('bpb'),
            'nkl' => $buatDatasetSnapshot('nkl', true),
            'stockout_dc' => $buatDatasetSnapshot('stockout_dc'),
            'stockout_toko' => $buatDatasetSnapshot('stockout_toko'),
        ];
    }

    private function susunDataTrend($laporans, Carbon $awal, int $periode): array
    {
        $perTanggal = $laporans->groupBy(
            fn ($row) => Carbon::parse($row->tanggal_laporan)->toDateString()
        );

        $labels = [];
        $npb = [];
        $bpb = [];

        for ($i = 0; $i < $periode; $i++) {
            $tanggal = $awal->copy()->addDays($i);
            $dataHari = $perTanggal->get($tanggal->toDateString(), collect());

            $nilaiNpb = $dataHari->map(fn ($row) => $row->operasional?->npb_all)
                ->filter(fn ($v) => $v !== null);
            $nilaiBpb = $dataHari->map(fn ($row) => $row->operasional?->bpb_all)
                ->filter(fn ($v) => $v !== null);

            $labels[] = $tanggal->format('d/m');
            $npb[] = $nilaiNpb->isEmpty() ? null : (float) $nilaiNpb->sum();
            $bpb[] = $nilaiBpb->isEmpty() ? null : (float) $nilaiBpb->sum();
        }

        return [
            'labels' => $labels,
            'npb' => ['aktual' => $npb, 'ma3' => $this->movingAverageTiga($npb)],
            'bpb' => ['aktual' => $bpb, 'ma3' => $this->movingAverageTiga($bpb)],
        ];
    }

    private function movingAverageTiga(array $nilaiHarian): array
    {
        $hasil = [];
        $jendela = [];

        foreach ($nilaiHarian as $nilai) {
            if ($nilai === null) {
                $jendela = [];
                $hasil[] = null;
                continue;
            }

            $jendela[] = (float) $nilai;
            if (count($jendela) > 3) {
                array_shift($jendela);
            }

            $hasil[] = count($jendela) === 3 ? array_sum($jendela) / 3 : null;
        }

        return $hasil;
    }

    private function tanggalAtauHariIni(?string $input): Carbon
    {
        $hariIni = now()->startOfDay();

        if (!$input) {
            return $hariIni;
        }

        $tanggal = Carbon::createFromFormat('!Y-m-d', $input);
        return $tanggal->gt($hariIni) ? $hariIni : $tanggal;
    }
}
