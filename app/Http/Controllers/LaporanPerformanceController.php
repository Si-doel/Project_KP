<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class LaporanPerformanceController extends Controller
{
    // Angka bulat besar (bigInteger), boleh negatif
    private const ANGKA = [
        'produktivitas_akumulasi',
        'proforma_beban_tk_all',
        'proforma_beban_dc_perish',
        'proforma_dry_whs',
        'proforma_dry_issuing',
        'proforma_dry_expedisi',
        'proforma_dry_admin',
        'proforma_dry_lain2',
        'revisi_scanner_pick',
        'revisi_scanner_scan',
        'so_rp_preadjust1',
        'so_rp_adjust',
        'nkl_dry_ba',
        'barang_rusak_pabrik_beban_toko',
        'tertinggal_intransit_b',
    ];

    // Angka bulat biasa (integer), boleh negatif
    private const ANGKA_KECIL = ['tertinggal_container', 'tertinggal_bronjong'];

    // Persentase 0 - 100
    private const PERSEN = ['sl_pick_item', 'sl_pick_qty', 'sl_npb_pb_item', 'sl_npb_pb_qty'];

    // Tanggal + jam
    private const WAKTU = ['so_jam_upload', 'so_jam_preadjust1', 'so_jam_adjust'];

    /** Tampilkan form (terisi jika data sudah pernah disimpan). */
    public function edit(string $tanggal)
    {
        $tgl  = $this->parseTanggal($tanggal);
        $user = $this->user();

        $laporan = LaporanHarian::with('performance')
            ->where('cabang_id', $user->cabang_id)
            ->where('tanggal_laporan', $tgl->toDateString())
            ->first();

        return view('laporan.performance', [
            'tgl'     => $tgl,
            'cabang'  => $user->cabang,
            'data'    => $laporan?->performance,
            'lengkap' => $laporan?->prf_selesai_at !== null,
        ]);
    }

    /**
     * Tombol Simpan: semua isian wajib terisi. Kalau ada yang kosong atau salah,
     * tidak ada yang disimpan dan pesan error ditampilkan.
     * Boleh disimpan ulang (revisi) selama laporan belum dikirim.
     */
    public function update(Request $request, string $tanggal)
    {
        $tgl  = $this->parseTanggal($tanggal);
        $user = $this->user();

        // Validasi dulu: kalau gagal, tidak ada yang dibuat di database
        $data = $request->validate($this->rules($tgl), $this->messages(), $this->labels());

        DB::transaction(function () use ($user, $tgl, $data) {
            // Baris induk dibuat saat simpan pertama kali (start_proses terisi di sini)
            $laporan = LaporanHarian::firstOrCreate(
                ['cabang_id' => $user->cabang_id, 'tanggal_laporan' => $tgl->toDateString()],
                ['user_id' => $user->id, 'start_proses' => now()]
            );

            // Satu laporan = satu baris performance: buat jika belum ada, ubah jika sudah ada
            $laporan->performance()->updateOrCreate([], $data);

            // Simpan hanya lolos kalau semua isian lengkap, jadi bagian ini pasti lengkap.
            // Waktu lengkap pertama dicatat sekali dan tidak berubah saat revisi.
            if ($laporan->prf_selesai_at === null) {
                $laporan->update(['prf_selesai_at' => now()]);
            }
        });

        return redirect()
            ->route('laporan.performance.edit', $tgl->toDateString())
            ->with('success', 'Laporan Performance tersimpan.');
    }

    /** Aturan: semua isian wajib. */
    private function rules(Carbon $tgl): array
    {
        $rules = [];

        foreach (self::ANGKA as $f) {
            $rules[$f] = ['required', 'integer'];
        }

        foreach (self::ANGKA_KECIL as $f) {
            $rules[$f] = ['required', 'integer', 'between:-2147483648,2147483647'];
        }

        foreach (self::PERSEN as $f) {
            $rules[$f] = ['required', 'numeric', 'between:0,100', 'decimal:0,2'];
        }

        // Waktu stock opname harus wajar: dari tanggal laporan sampai 2 hari sesudahnya
        $awal  = $tgl->copy()->startOfDay()->toDateTimeString();
        $akhir = $tgl->copy()->addDays(2)->endOfDay()->toDateTimeString();

        foreach (self::WAKTU as $f) {
            $rules[$f] = ['required', 'date', 'after_or_equal:' . $awal, 'before_or_equal:' . $akhir];
        }

        // Durasi time picking (jam:menit:detik)
        $rules['time_picking'] = ['required', 'date_format:H:i:s,H:i'];

        return $rules;
    }

    /** Pesan error berbahasa Indonesia untuk form ini. */
    private function messages(): array
    {
        return [
            'required'        => ':attribute wajib diisi.',
            'integer'         => ':attribute harus berupa angka bulat.',
            'numeric'         => ':attribute harus berupa angka.',
            'between'         => ':attribute harus antara :min dan :max.',
            'decimal'         => ':attribute maksimal 2 angka di belakang koma.',
            'date'            => ':attribute harus berupa tanggal dan jam yang valid.',
            'date_format'     => ':attribute harus berformat jam:menit:detik, contoh 00:02:30.',
            'after_or_equal'  => ':attribute harus di antara tanggal laporan dan 2 hari sesudahnya.',
            'before_or_equal' => ':attribute harus di antara tanggal laporan dan 2 hari sesudahnya.',
        ];
    }

    /** Nama isian yang tampil di pesan error. */
    private function labels(): array
    {
        return [
            'produktivitas_akumulasi'        => 'Produktivitas (akumulasi)',
            'proforma_beban_tk_all'          => 'Proforma beban TK (ALL)',
            'proforma_beban_dc_perish'       => 'Proforma beban DC (PERISH)',
            'proforma_dry_whs'               => 'Proforma DRY WHS',
            'proforma_dry_issuing'           => 'Proforma DRY Issuing',
            'proforma_dry_expedisi'          => 'Proforma DRY Expedisi',
            'proforma_dry_admin'             => 'Proforma DRY Admin',
            'proforma_dry_lain2'             => 'Proforma DRY Lain-lain',
            'sl_pick_item'                   => 'SL Pick (item)',
            'sl_pick_qty'                    => 'SL Pick (qty)',
            'sl_npb_pb_item'                 => 'SL NPB vs PB (item)',
            'sl_npb_pb_qty'                  => 'SL NPB vs PB (qty)',
            'revisi_scanner_pick'            => 'Revisi scanner Pick',
            'revisi_scanner_scan'            => 'Revisi scanner Scan',
            'so_jam_upload'                  => 'Stock opname jam upload',
            'so_jam_preadjust1'              => 'Stock opname jam preadjust 1',
            'so_rp_preadjust1'               => 'Stock opname Rp preadjust 1',
            'so_jam_adjust'                  => 'Stock opname jam adjust',
            'so_rp_adjust'                   => 'Stock opname Rp adjust',
            'nkl_dry_ba'                     => 'NKL DRY BA',
            'barang_rusak_pabrik_beban_toko' => 'Barang rusak pabrik (beban toko)',
            'time_picking'                   => 'Time picking',
            'tertinggal_container'           => 'Barang tertinggal Container',
            'tertinggal_bronjong'            => 'Barang tertinggal Bronjong',
            'tertinggal_intransit_b'         => 'Barang tertinggal Intransit B',
        ];
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
