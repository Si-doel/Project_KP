<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanUtilitas extends Model
{
    // Nama tabel ditulis eksplisit: "utilitas" sudah berakhiran s, jadi Laravel bisa salah menjamakkan
    protected $table = 'laporan_utilitas';

    protected $fillable = [
        'laporan_harian_id',
        'forklift_aktif',
        'forklift_rusak',
        'stacker_aktif',
        'stacker_rusak',
        'php_aktif',
        'php_rusak',
    ];

    public function laporanHarian()
    {
        return $this->belongsTo(LaporanHarian::class);
    }
}
