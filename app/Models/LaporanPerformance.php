<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanPerformance extends Model
{
    protected $fillable = [
        'laporan_harian_id',
        'produktivitas_akumulasi',
        'proforma_beban_tk_all',
        'proforma_beban_dc_perish',
        'proforma_dry_whs',
        'proforma_dry_issuing',
        'proforma_dry_expedisi',
        'proforma_dry_admin',
        'proforma_dry_lain2',
        'sl_pick_item',
        'sl_pick_qty',
        'sl_npb_pb_item',
        'sl_npb_pb_qty',
        'revisi_scanner_pick',
        'revisi_scanner_scan',
        'so_jam_upload',
        'so_jam_preadjust1',
        'so_rp_preadjust1',
        'so_jam_adjust',
        'so_rp_adjust',
        'nkl_dry_ba',
        'barang_rusak_pabrik_beban_toko',
        'time_picking',
        'tertinggal_container',
        'tertinggal_bronjong',
        'tertinggal_intransit_b',
    ];

    protected $casts = [
        'sl_pick_item'      => 'decimal:2',
        'sl_pick_qty'       => 'decimal:2',
        'sl_npb_pb_item'    => 'decimal:2',
        'sl_npb_pb_qty'     => 'decimal:2',
        'so_jam_upload'     => 'datetime',
        'so_jam_preadjust1' => 'datetime',
        'so_jam_adjust'     => 'datetime',
    ];

    public function laporanHarian()
    {
        return $this->belongsTo(LaporanHarian::class);
    }

    /** TTL harian proforma DRY: dihitung, tidak disimpan. */
    public function getProformaDryTotalAttribute(): int
    {
        return (int) $this->proforma_dry_whs
            + (int) $this->proforma_dry_issuing
            + (int) $this->proforma_dry_expedisi
            + (int) $this->proforma_dry_admin
            + (int) $this->proforma_dry_lain2;
    }
}
