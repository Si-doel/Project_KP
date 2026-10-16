<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanHarian extends Model
{
    protected $fillable = [
        'cabang_id',
        'tanggal_laporan',
        'user_id',
        'start_proses',
        'end_proses',
        'opr_selesai_at',
        'prf_selesai_at',
        'inv_selesai_at',
        'kry_selesai_at',
        'utl_selesai_at',
        'arm_selesai_at',
    ];

    protected $casts = [
        'tanggal_laporan' => 'date',
        'start_proses'    => 'datetime',
        'end_proses'      => 'datetime',
        'opr_selesai_at'  => 'datetime',
        'prf_selesai_at'  => 'datetime',
        'inv_selesai_at'  => 'datetime',
        'kry_selesai_at'  => 'datetime',
        'utl_selesai_at'  => 'datetime',
        'arm_selesai_at'  => 'datetime',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function operasional()
    {
        return $this->hasOne(LaporanOperasional::class);
    }

    public function performance()
    {
        return $this->hasOne(LaporanPerformance::class);
    }
}
