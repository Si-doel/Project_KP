<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanOperasionalController extends Controller
{
    // Kelompok isian, dipakai untuk menyusun aturan validasi
    private const JUMLAH = [
        'jumlah_toko',
        'toko_picking',
        'toko_kirim',
        'paket_terbentuk',
        'paket_kirim',
        'supplier_daftar',
        'supplier_pending',
    ];

    private const RUPIAH = ['npb_all', 'npb_dry', 'bpb_all'];

    private const WAKTU = [
        'waktu_selesai_rcv',
        'waktu_selesai_picking',
        'waktu_selesai_loading',
        'waktu_selesai_del',
    ];

    /** Tampilkan form (terisi jika data sudah pernah disimpan). */
    public function edit(string $tanggal)
    {
        $tgl  = $this->parseTanggal($tanggal);
        $user = $this->user();

        $laporan = LaporanHarian::with('operasional')
            ->where('cabang_id', $user->cabang_id)
            ->where('tanggal_laporan', $tgl->toDateString())
            ->first();

        return view('laporan.operasional', [
            'tgl'     => $tgl,
            'cabang'  => $user->cabang,
            'data'    => $laporan?->operasional,
            'lengkap' => $laporan?->opr_selesai_at !== null,
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

            // Satu laporan = satu baris operasional: buat jika belum ada, ubah jika sudah ada
            $laporan->operasional()->updateOrCreate([], $data);

            // Simpan hanya lolos kalau semua isian lengkap, jadi bagian ini pasti lengkap.
            // Waktu lengkap pertama dicatat sekali dan tidak berubah saat revisi.
            if ($laporan->opr_selesai_at === null) {
                $laporan->update(['opr_selesai_at' => now()]);
            }
        });

        return redirect()
            ->route('laporan.operasional.edit', $tgl->toDateString())
            ->with('success', 'Laporan Operasional tersimpan.');
    }

    /** Aturan: semua isian wajib. */
    private function rules(Carbon $tgl): array
    {
        $rules = [];

        foreach (self::JUMLAH as $f) {
            $rules[$f] = ['required', 'integer', 'min:0', 'max:4294967295'];
        }

        foreach (self::RUPIAH as $f) {
            $rules[$f] = ['required', 'integer', 'min:0'];
        }

        // Waktu selesai harus wajar: dari tanggal laporan sampai 2 hari sesudahnya
        $awal  = $tgl->copy()->startOfDay()->toDateTimeString();
        $akhir = $tgl->copy()->addDays(2)->endOfDay()->toDateTimeString();

        foreach (self::WAKTU as $f) {
            $rules[$f] = ['required', 'date', 'after_or_equal:' . $awal, 'before_or_equal:' . $akhir];
        }

        return $rules;
    }

    /** Pesan error berbahasa Indonesia untuk form ini. */
    private function messages(): array
    {
        return [
            'required'        => ':attribute wajib diisi.',
            'integer'         => ':attribute harus berupa angka bulat.',
            'min'             => ':attribute tidak boleh kurang dari :min.',
            'max'             => ':attribute terlalu besar.',
            'date'            => ':attribute harus berupa tanggal dan jam yang valid.',
            'after_or_equal'  => ':attribute harus di antara tanggal laporan dan 2 hari sesudahnya.',
            'before_or_equal' => ':attribute harus di antara tanggal laporan dan 2 hari sesudahnya.',
        ];
    }

    /** Nama isian yang tampil di pesan error. */
    private function labels(): array
    {
        return [
            'jumlah_toko'           => 'Jumlah toko',
            'toko_picking'          => 'Jumlah toko picking',
            'toko_kirim'            => 'Jumlah toko kirim',
            'npb_all'               => 'NPB ALL',
            'npb_dry'               => 'NPB DRY',
            'bpb_all'               => 'BPB ALL',
            'paket_terbentuk'       => 'Paket terbentuk',
            'paket_kirim'           => 'Paket kirim',
            'supplier_daftar'       => 'Supplier daftar',
            'supplier_pending'      => 'Supplier pending',
            'waktu_selesai_rcv'     => 'Waktu selesai Rcv',
            'waktu_selesai_picking' => 'Waktu selesai Picking',
            'waktu_selesai_loading' => 'Waktu selesai Loading',
            'waktu_selesai_del'     => 'Waktu selesai Del',
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
