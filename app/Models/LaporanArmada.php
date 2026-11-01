<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanArmada extends Model
{
    // Nama tabel ditulis eksplisit agar sama persis dengan migration
    protected $table = 'laporan_armadas';

    protected $fillable = [
        'laporan_harian_id',
        'dry_aktif',
        'dry_rusak',
        'roti_aktif',
        'roti_rusak',
        'buah_aktif',
        'buah_rusak',
        'other_aktif',
        'other_rusak',
    ];

    public function laporanHarian()
    {
        return $this->belongsTo(LaporanHarian::class);
    }
}
