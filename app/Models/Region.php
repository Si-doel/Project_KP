<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_region',
        'nama_pejabat',
        'jabatan_pejabat',
    ];

    public function cabangs()
    {
        return $this->hasMany(Cabang::class);
    }

    public const JABATAN = [
        'Logistik Manager',
        'Logistik Area Manager',
        'DC Manager',
    ];
}
