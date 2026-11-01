<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanArmadaController extends Controller
{
    // Semua isian: jumlah unit, angka bulat nol atau lebih
    private const ISIAN = [
        'dry_aktif',
        'dry_rusak',
        'roti_aktif',
        'roti_rusak',
        'buah_aktif',
        'buah_rusak',
        'other_aktif',
        'other_rusak',
    ];

    /** Tampilkan form (terisi jika data sudah pernah disimpan). */
    public function edit(string $tanggal)
    {
        $tgl  = $this->parseTanggal($tanggal);
        $user = $this->user();

        $laporan = LaporanHarian::with('armada')
            ->where('cabang_id', $user->cabang_id)
            ->where('tanggal_laporan', $tgl->toDateString())
            ->first();

        return view('laporan.armada', [
            'tgl'     => $tgl,
            'cabang'  => $user->cabang,
            'data'    => $laporan?->armada,
            'lengkap' => $laporan?->arm_selesai_at !== null,
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

            // Satu laporan = satu baris armada: buat jika belum ada, ubah jika sudah ada
            $laporan->armada()->updateOrCreate([], $data);

            // Simpan hanya lolos kalau semua isian lengkap, jadi bagian ini pasti lengkap.
            // Waktu lengkap pertama dicatat sekali dan tidak berubah saat revisi.
            if ($laporan->arm_selesai_at === null) {
                $laporan->update(['arm_selesai_at' => now()]);
            }
        });

        return redirect()
            ->route('laporan.armada.edit', $tgl->toDateString())
            ->with('success', 'Laporan Armada tersimpan.');
    }

    /** Aturan: semua isian wajib, angka bulat nol atau lebih. */
    private function rules(): array
    {
        $rules = [];

        foreach (self::ISIAN as $f) {
            $rules[$f] = ['required', 'integer', 'min:0', 'max:4294967295'];
        }

        return $rules;
    }

    /** Pesan error berbahasa Indonesia untuk form ini. */
    private function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'integer'  => ':attribute harus berupa angka bulat.',
            'min'      => ':attribute tidak boleh kurang dari :min.',
            'max'      => ':attribute terlalu besar.',
        ];
    }

    /** Nama isian yang tampil di pesan error. */
    private function labels(): array
    {
        return [
            'dry_aktif'   => 'Armada Dry aktif',
            'dry_rusak'   => 'Armada Dry rusak',
            'roti_aktif'  => 'Armada Roti aktif',
            'roti_rusak'  => 'Armada Roti rusak',
            'buah_aktif'  => 'Armada Buah aktif',
            'buah_rusak'  => 'Armada Buah rusak',
            'other_aktif' => 'Armada Other aktif',
            'other_rusak' => 'Armada Other rusak',
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
