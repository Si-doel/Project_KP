<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_cabang',
        'nama_cabang',
        'alamat',
        'kota_kabupaten',
        'provinsi',
        'jenis_cabang',
        'region_id',
        'latitude',
        'longitude',
        'status',
    ];

    // protected $casts = [
    //     'status' => 'boolean',
    // ];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public const PROVINSI = [
        'ACEH',
        'SUMATERA UTARA',
        'SUMATERA BARAT',
        'RIAU',
        'KEPULAUAN RIAU',
        'JAMBI',
        'SUMATERA SELATAN',
        'KEPULAUAN BANGKA BELITUNG',
        'BENGKULU',
        'LAMPUNG',
        'DKI JAKARTA',
        'BANTEN',
        'JAWA BARAT',
        'JAWA TENGAH',
        'DI YOGYAKARTA',
        'JAWA TIMUR',
        'BALI',
        'NUSA TENGGARA BARAT',
        'NUSA TENGGARA TIMUR',
        'KALIMANTAN BARAT',
        'KALIMANTAN TENGAH',
        'KALIMANTAN SELATAN',
        'KALIMANTAN TIMUR',
        'KALIMANTAN UTARA',
        'SULAWESI UTARA',
        'GORONTALO',
        'SULAWESI TENGAH',
        'SULAWESI BARAT',
        'SULAWESI SELATAN',
        'SULAWESI TENGGARA',
        'MALUKU',
        'MALUKU UTARA',
        'PAPUA',
        'PAPUA BARAT',
        'PAPUA BARAT DAYA',
        'PAPUA SELATAN',
        'PAPUA TENGAH',
        'PAPUA PEGUNUNGAN',
    ];

    public static function daftarProvinsi(): array
    {
        $list = self::PROVINSI;
        sort($list);

        return $list;
    }
}
