<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanInventory extends Model
{
    protected $fillable = [
        'laporan_harian_id',
        'stock_out_toko',
        'stock_out_dc',
        'intransit_nrb_proforma',
        'nrb_fisik_di_dc',
        'nrb_fisik_di_toko',
        'intransit_a',
        'nrb_ke_supplier',
        'nrb_sepihak_sudah_diambil',
        'nrb_sepihak_belum_diambil',
    ];

    public function laporanHarian()
    {
        return $this->belongsTo(LaporanHarian::class);
    }
}
