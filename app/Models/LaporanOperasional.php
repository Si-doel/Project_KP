<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanOperasional extends Model
{
    protected $fillable = [
        'laporan_harian_id',
        'jumlah_toko',
        'npb_all',
        'npb_dry',
        'bpb_all',
        'toko_picking',
        'toko_kirim',
        'paket_terbentuk',
        'paket_kirim',
        'supplier_daftar',
        'supplier_pending',
        'waktu_selesai_rcv',
        'waktu_selesai_picking',
        'waktu_selesai_loading',
        'waktu_selesai_del',
    ];

    protected $casts = [
        'waktu_selesai_rcv'     => 'datetime',
        'waktu_selesai_picking' => 'datetime',
        'waktu_selesai_loading' => 'datetime',
        'waktu_selesai_del'     => 'datetime',
    ];

    public function laporanHarian()
    {
        return $this->belongsTo(LaporanHarian::class);
    }
}
