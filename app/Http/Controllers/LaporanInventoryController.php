<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanInventoryController extends Controller
{
    // Angka bulat besar (bigInteger), boleh negatif
    private const ANGKA = [
        'intransit_nrb_proforma',
        'nrb_fisik_di_dc',
        'nrb_fisik_di_toko',
        'intransit_a',
        'nrb_ke_supplier',
        'nrb_sepihak_sudah_diambil',
        'nrb_sepihak_belum_diambil',
    ];

    // Angka bulat biasa (integer), boleh negatif
    private const ANGKA_KECIL = ['stock_out_toko', 'stock_out_dc'];

    /** Tampilkan form (terisi jika data sudah pernah disimpan). */
    public function edit(string $tanggal)
    {
        $tgl  = $this->parseTanggal($tanggal);
        $user = $this->user();

        $laporan = LaporanHarian::with('inventory')
            ->where('cabang_id', $user->cabang_id)
            ->where('tanggal_laporan', $tgl->toDateString())
            ->first();

        return view('laporan.inventory', [
            'tgl'     => $tgl,
            'cabang'  => $user->cabang,
            'data'    => $laporan?->inventory,
            'lengkap' => $laporan?->inv_selesai_at !== null,
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
        $data = $request->validate($this->rules(), $this->messages(), $this->labels());

        DB::transaction(function () use ($user, $tgl, $data) {
            // Baris induk dibuat saat simpan pertama kali (start_proses terisi di sini)
            $laporan = LaporanHarian::firstOrCreate(
                ['cabang_id' => $user->cabang_id, 'tanggal_laporan' => $tgl->toDateString()],
                ['user_id' => $user->id, 'start_proses' => now()]
            );

            // Satu laporan = satu baris inventory: buat jika belum ada, ubah jika sudah ada
            $laporan->inventory()->updateOrCreate([], $data);

            // Simpan hanya lolos kalau semua isian lengkap, jadi bagian ini pasti lengkap.
            // Waktu lengkap pertama dicatat sekali dan tidak berubah saat revisi.
            if ($laporan->inv_selesai_at === null) {
                $laporan->update(['inv_selesai_at' => now()]);
            }
        });

        return redirect()
            ->route('laporan.inventory.edit', $tgl->toDateString())
            ->with('success', 'Laporan Inventory tersimpan.');
    }

    /** Aturan: semua isian wajib. */
    private function rules(): array
    {
        $rules = [];

        foreach (self::ANGKA as $f) {
            $rules[$f] = ['required', 'integer'];
        }

        foreach (self::ANGKA_KECIL as $f) {
            $rules[$f] = ['required', 'integer', 'between:-2147483648,2147483647'];
        }

        return $rules;
    }

    /** Pesan error berbahasa Indonesia untuk form ini. */
    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer'  => ':attribute harus berupa angka bulat.',
            'between'  => ':attribute harus antara :min dan :max.',
        ];
    }

    /** Nama isian yang tampil di pesan error. */
    private function labels(): array
    {
        return [
            'stock_out_toko'            => 'Stock out toko',
            'stock_out_dc'              => 'Stock out DC',
            'intransit_nrb_proforma'    => 'Intransit NRB proforma ALL',
            'nrb_fisik_di_dc'           => 'NRB fisik di DC',
            'nrb_fisik_di_toko'         => 'NRB fisik di toko',
            'intransit_a'               => 'Intransit A',
            'nrb_ke_supplier'           => 'NRB ke supplier',
            'nrb_sepihak_sudah_diambil' => 'NRB sepihak sudah diambil',
            'nrb_sepihak_belum_diambil' => 'NRB sepihak belum diambil',
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
