<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarian;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanKaryawanController extends Controller
{
    // Semua isian: angka bulat nol atau lebih (jumlah orang dan jam lembur)
    private const ISIAN = [
        'hadir',
        'off',
        'cuti',
        'alpa',
        'sakit',
        'lembur_rcv_ret',
        'lembur_whs',
        'lembur_iss',
        'lembur_perish',
        'lembur_adm',
    ];

    /** Tampilkan form (terisi jika data sudah pernah disimpan). */
    public function edit(string $tanggal)
    {
        $tgl  = $this->parseTanggal($tanggal);
        $user = $this->user();

        $laporan = LaporanHarian::with('karyawan')
            ->where('cabang_id', $user->cabang_id)
            ->where('tanggal_laporan', $tgl->toDateString())
            ->first();

        return view('laporan.karyawan', [
            'tgl'     => $tgl,
            'cabang'  => $user->cabang,
            'data'    => $laporan?->karyawan,
            'lengkap' => $laporan?->kry_selesai_at !== null,
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

            // Satu laporan = satu baris karyawan: buat jika belum ada, ubah jika sudah ada
            $laporan->karyawan()->updateOrCreate([], $data);

            // Simpan hanya lolos kalau semua isian lengkap, jadi bagian ini pasti lengkap.
            // Waktu lengkap pertama dicatat sekali dan tidak berubah saat revisi.
            if ($laporan->kry_selesai_at === null) {
                $laporan->update(['kry_selesai_at' => now()]);
            }
        });

        return redirect()
            ->route('laporan.karyawan.edit', $tgl->toDateString())
            ->with('success', 'Laporan Karyawan tersimpan.');
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
            'hadir'          => 'Jumlah hadir',
            'off'            => 'Jumlah off',
            'cuti'           => 'Jumlah cuti',
            'alpa'           => 'Jumlah alpa',
            'sakit'          => 'Jumlah sakit',
            'lembur_rcv_ret' => 'Jam lembur Rcv Ret',
            'lembur_whs'     => 'Jam lembur Whs',
            'lembur_iss'     => 'Jam lembur Iss',
            'lembur_perish'  => 'Jam lembur Perish',
            'lembur_adm'     => 'Jam lembur Adm',
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
