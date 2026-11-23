<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\LaporanHarian;
use App\Models\Region;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Sisi admin:
 * Memantau kelengkapan laporan semua cabang dan membuka kembali
 * laporan yang sudah dikirim.
 */
class LaporanAdminController extends Controller
{
    /** Jumlah cabang per halaman pada daftar kelengkapan. */
    private const PER_HALAMAN = 15;

    /**
     * Daftar kelengkapan laporan semua cabang aktif
     * berdasarkan tanggal dan filter yang dipilih.
     */
    public function index(Request $request)
    {
        // Validasi filter region dan status.
        $filter = $request->validate([
            'region_id' => [
                'nullable',
                'integer',
                Rule::exists('regions', 'id'),
            ],
            'status' => [
                'nullable',
                Rule::in(['FINAL', 'DRAFT', 'BELUM']),
            ],
        ]);

        // Tanggal kosong atau tidak valid akan menggunakan hari ini.
        $tgl = $this->tanggalAtauHariIni($request->query('tanggal'));

        // Mengambil seluruh cabang aktif.
        $cabangs = Cabang::with('region')
            ->where('status', 'AKTIF')
            ->when(
                $filter['region_id'] ?? null,
                fn($q, $id) => $q->where('region_id', $id)
            )
            ->orderBy('kode_cabang')
            ->get();

        // Mengambil laporan berdasarkan tanggal yang dipilih.
        // Hasil dikelompokkan berdasarkan cabang_id.
        $laporans = LaporanHarian::where('tanggal_laporan', $tgl->toDateString())
            ->get()
            ->keyBy('cabang_id');

        // Menentukan status laporan setiap cabang.
        // BELUM berarti belum ada record laporan.
        $statusDari = fn(Cabang $c) =>
        $laporans->get($c->id)?->status ?? 'BELUM';

        // Ringkasan dihitung sebelum filter status diterapkan.
        $ringkas = [
            'total' => $cabangs->count(),

            'final' => $cabangs->filter(
                fn($c) => $statusDari($c) === 'FINAL'
            )->count(),

            'draft' => $cabangs->filter(
                fn($c) => $statusDari($c) === 'DRAFT'
            )->count(),

            'belum' => $cabangs->filter(
                fn($c) => $statusDari($c) === 'BELUM'
            )->count(),
        ];

        // Filter status hanya memengaruhi tabel.
        if (!empty($filter['status'])) {
            $cabangs = $cabangs->filter(
                fn($c) => $statusDari($c) === $filter['status']
            );
        }

        // Pagination manual karena data telah difilter berdasarkan
        // status yang dihitung dari accessor model.
        $total = $cabangs->count();

        $terakhir = max(
            1,
            (int) ceil($total / self::PER_HALAMAN)
        );

        $halaman = min(
            max(1, LengthAwarePaginator::resolveCurrentPage()),
            $terakhir
        );

        $baris = new LengthAwarePaginator(
            $cabangs->forPage($halaman, self::PER_HALAMAN)->values(),
            $total,
            self::PER_HALAMAN,
            $halaman,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        return view('admin.laporan.index', [
            'tgl' => $tgl,
            'baris' => $baris,
            'laporans' => $laporans,
            'ringkas' => $ringkas,
            'regions' => Region::orderBy('nama_region')->get(),
        ]);
    }

    /**
     * Detail satu laporan:
     * kelengkapan enam bagian, waktu proses, dan tombol Buka Kembali.
     */
    public function show(LaporanHarian $laporan)
    {
        $laporan->load('cabang.region', 'user');

        return view('admin.laporan.show', compact('laporan'));
    }

    /**
     * Membuka kembali laporan yang sudah dikirim.
     * end_proses dikosongkan sehingga status kembali menjadi DRAFT.
     */
    public function buka(LaporanHarian $laporan)
    {
        // Proses pembukaan kembali dilakukan dalam transaksi.
        $berhasil = DB::transaction(function () use ($laporan) {

            // Mengunci record selama proses berlangsung.
            $dataLaporan = LaporanHarian::whereKey($laporan->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Jika belum pernah dikirim, tidak perlu dibuka kembali.
            if ($dataLaporan->end_proses === null) {
                return false;
            }

            // Mengosongkan waktu pengiriman.
            $dataLaporan->update([
                'end_proses' => null,
            ]);

            return true;
        });

        if (!$berhasil) {
            return back()->with(
                'error',
                'Laporan ini belum dikirim, jadi tidak perlu dibuka kembali.'
            );
        }

        return back()->with(
            'success',
            'Laporan dibuka kembali. Cabang dapat mengubahnya dan mengirim ulang.'
        );
    }

    /**
     * Mengambil tanggal dari parameter ?tanggal=Y-m-d.
     *
     * Jika kosong, salah format, tidak valid dalam kalender,
     * atau tanggal masa depan, gunakan tanggal hari ini.
     */
    private function tanggalAtauHariIni(mixed $input): Carbon
    {
        $hariIni = now()->startOfDay();

        if (
            !is_string($input)
            || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $input, $m)
            || !checkdate((int) $m[2], (int) $m[3], (int) $m[1])
        ) {
            return $hariIni;
        }

        $tgl = Carbon::createFromFormat('!Y-m-d', $input);

        return $tgl->gt($hariIni) ? $hariIni : $tgl;
    }
}
