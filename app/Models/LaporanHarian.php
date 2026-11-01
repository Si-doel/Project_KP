<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanHarian extends Model
{
    /** Kode bagian => nama bagian (urutan = urutan tampil). */
    public const BAGIAN = [
        'opr' => 'Operasional',
        'prf' => 'Performance',
        'inv' => 'Inventory',
        'kry' => 'Karyawan',
        'utl' => 'Utilitas',
        'arm' => 'Armada',
    ];

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

    public function inventory()
    {
        return $this->hasOne(LaporanInventory::class);
    }

    public function karyawan()
    {
        return $this->hasOne(LaporanKaryawan::class);
    }

    public function utilitas()
    {
        return $this->hasOne(LaporanUtilitas::class);
    }

    public function armada()
    {
        return $this->hasOne(LaporanArmada::class);
    }

    // ---------- Status (dihitung, tidak disimpan) ----------

    /** Apakah bagian ini sudah terisi lengkap? (penanda *_selesai_at terisi saat Simpan lolos) */
    public function sudahLengkap(string $kode): bool
    {
        return array_key_exists($kode, self::BAGIAN)
            && $this->{$kode . '_selesai_at'} !== null;
    }

    /** Jumlah bagian yang sudah lengkap (0 sampai 6). */
    public function jumlahLengkap(): int
    {
        $jumlah = 0;
        foreach (array_keys(self::BAGIAN) as $kode) {
            if ($this->{$kode . '_selesai_at'} !== null) {
                $jumlah++;
            }
        }

        return $jumlah;
    }

    /** Semua bagian lengkap tetapi laporan belum dikirim. */
    public function siapKirim(): bool
    {
        return $this->jumlahLengkap() === count(self::BAGIAN) && $this->end_proses === null;
    }

    /** FINAL jika sudah dikirim (end_proses terisi), selain itu DRAFT. */
    public function getStatusAttribute(): string
    {
        return $this->end_proses !== null ? 'FINAL' : 'DRAFT';
    }
}
