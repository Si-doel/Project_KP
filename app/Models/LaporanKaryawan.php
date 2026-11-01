<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanKaryawan extends Model
{
    protected $fillable = [
        'laporan_harian_id',
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

    public function laporanHarian()
    {
        return $this->belongsTo(LaporanHarian::class);
    }

    /** Total karyawan tercatat: dihitung, tidak disimpan. */
    public function getTotalKaryawanAttribute(): int
    {
        return (int) $this->hadir + (int) $this->off + (int) $this->cuti
            + (int) $this->alpa + (int) $this->sakit;
    }

    /** Total jam lembur semua bagian: dihitung, tidak disimpan. */
    public function getTotalLemburAttribute(): int
    {
        return (int) $this->lembur_rcv_ret + (int) $this->lembur_whs
            + (int) $this->lembur_iss + (int) $this->lembur_perish + (int) $this->lembur_adm;
    }
}
