<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\LaporanHarian;
use App\Models\Region;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Master Cabang (khusus admin): daftar, tambah, ubah, hapus data cabang.
 * Bukan dashboard operator cabang (itu DashboardCabangController).
 */
class CabangController extends Controller
{
    public function index(Request $request)
    {
        $cabangs = Cabang::with('region')
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('nama_cabang', 'like', "%{$q}%")
                        ->orWhere('kode_cabang', 'like', "%{$q}%");
                });
            })
            ->orderBy('kode_cabang')
            ->paginate(15)
            ->withQueryString();

        return view('cabangs.index', compact('cabangs'));
    }

    public function create()
    {
        return view('cabangs.form', [
            'cabang'  => new Cabang(),
            'regions' => Region::orderBy('nama_region')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Cabang::create($this->validated($request));

        return redirect()->route('cabangs.index')
            ->with('success', 'Data cabang berhasil ditambahkan.');
    }

    public function edit(Cabang $cabang)
    {
        return view('cabangs.form', [
            'cabang'  => $cabang,
            'regions' => Region::orderBy('nama_region')->get(),
        ]);
    }

    public function update(Request $request, Cabang $cabang)
    {
        $cabang->update($this->validated($request, $cabang));

        return redirect()->route('cabangs.index')
            ->with('success', 'Data cabang berhasil diperbarui.');
    }

    public function destroy(Cabang $cabang)
    {
        if (User::where('cabang_id', $cabang->id)->exists()) {
            return back()->with('error', 'Cabang masih memiliki akun user. Ubah statusnya menjadi TUTUP, atau pindahkan usernya dulu.');
        }

        if (LaporanHarian::where('cabang_id', $cabang->id)->exists()) {
            return back()->with('error', 'Cabang sudah memiliki laporan harian, jadi tidak bisa dihapus. Ubah statusnya menjadi TUTUP.');
        }

        $cabang->delete();

        return back()->with('success', 'Data cabang berhasil dihapus.');
    }


    private function validated(Request $request, ?Cabang $cabang = null): array
    {
        // Normalisasi input
        $request->merge([
            'kode_cabang'    => strtoupper(trim((string) $request->input('kode_cabang', ''))),
            'nama_cabang'    => trim((string) $request->input('nama_cabang', '')),
            'alamat'         => trim((string) $request->input('alamat', '')),
            'kota_kabupaten' => strtoupper(trim((string) $request->input('kota_kabupaten', ''))),
            'provinsi'       => trim((string) $request->input('provinsi', '')),
        ]);

        return $request->validate([
            'kode_cabang' => [
                'required',
                'string',
                'size:4',
                'regex:/^[A-Z0-9]{4}$/',
                Rule::unique('cabangs', 'kode_cabang')
                    ->ignore($cabang?->id),
            ],

            'nama_cabang' => [
                'required',
                'string',
                'max:100',
            ],

            'alamat' => [
                'required',
                'string',
                'max:255',
            ],

            'kota_kabupaten' => [
                'required',
                'string',
                'max:50',
            ],

            'provinsi' => [
                'required',
                Rule::in(Cabang::PROVINSI),
            ],

            'jenis_cabang' => [
                'required',
                Rule::in(Cabang::JENIS_CABANG),
            ],

            'region_id' => [
                'required',
                'integer',
                'exists:regions,id',
            ],

            'latitude' => [
                'nullable',
                'required_with:longitude',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'required_with:latitude',
                'numeric',
                'between:-180,180',
            ],

            'status' => [
                'required',
                Rule::in(Cabang::STATUS),
            ],

        ], [
            'kode_cabang.required' => 'Kode cabang wajib diisi.',
            'kode_cabang.size' => 'Kode cabang harus tepat 4 karakter.',
            'kode_cabang.regex' => 'Kode cabang hanya boleh menggunakan huruf kapital dan angka, contoh: G259.',
            'kode_cabang.unique' => 'Kode cabang tersebut sudah terdaftar.',

            'nama_cabang.required' => 'Nama cabang wajib diisi.',
            'nama_cabang.max' => 'Nama cabang maksimal 100 karakter.',

            'alamat.required' => 'Alamat cabang wajib diisi.',
            'alamat.max' => 'Alamat maksimal 255 karakter.',

            'kota_kabupaten.required' => 'Kota/kabupaten wajib diisi.',
            'kota_kabupaten.max' => 'Kota/kabupaten maksimal 50 karakter.',

            'provinsi.required' => 'Provinsi wajib dipilih.',
            'provinsi.in' => 'Provinsi yang dipilih tidak valid.',

            'jenis_cabang.required' => 'Jenis cabang wajib dipilih.',
            'jenis_cabang.in' => 'Jenis cabang yang dipilih tidak valid.',

            'region_id.required' => 'Region wajib dipilih.',
            'region_id.integer' => 'Region yang dipilih tidak valid.',
            'region_id.exists' => 'Region yang dipilih tidak ditemukan.',

            'latitude.required_with' => 'Latitude wajib diisi jika longitude diisi.',
            'latitude.numeric' => 'Latitude harus berupa angka.',
            'latitude.between' => 'Latitude harus berada antara -90 sampai 90.',

            'longitude.required_with' => 'Longitude wajib diisi jika latitude diisi.',
            'longitude.numeric' => 'Longitude harus berupa angka.',
            'longitude.between' => 'Longitude harus berada antara -180 sampai 180.',

            'status.required' => 'Status cabang wajib dipilih.',
            'status.in' => 'Status cabang yang dipilih tidak valid.',
        ]);
    }
}
