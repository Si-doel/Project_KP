<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CabangController extends Controller
{
    public function index(Request $request)
    {
        // Kolom yang boleh digunakan untuk sorting
        $allowedSorts = [
            'kode_cabang' => 'cabangs.kode_cabang',
            'nama_cabang' => 'cabangs.nama_cabang',
            'kota_kabupaten' => 'cabangs.kota_kabupaten',
            'jenis_cabang' => 'cabangs.jenis_cabang',
            'region' => 'regions.nama_region',
            'status' => 'cabangs.status',
        ];

        $sort = $request->input('sort', 'kode_cabang');
        $direction = $request->input('direction', 'asc');

        // Validasi kolom dan arah sorting
        if (!array_key_exists($sort, $allowedSorts)) {
            $sort = 'kode_cabang';
        }

        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $query = Cabang::query()
            ->leftJoin('regions', 'cabangs.region_id', '=', 'regions.id')
            ->select('cabangs.*')
            ->with('region');

        // Pencarian kode atau nama cabang
        if ($request->filled('q')) {
            $keyword = $request->q;

            $query->where(function ($q) use ($keyword) {
                $q->where('cabangs.kode_cabang', 'like', "%{$keyword}%")
                    ->orWhere('cabangs.nama_cabang', 'like', "%{$keyword}%");
            });
        }

        // Sorting
        $query->orderBy($allowedSorts[$sort], $direction);

        // Pagination tetap mempertahankan pencarian dan sorting
        $cabangs = $query->paginate(10)->withQueryString();

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
        if ($cabang->users()->exists()) {
            return back()->with('error', 'Cabang masih memiliki akun user. Ubah statusnya menjadi TUTUP, atau pindahkan usernya dulu.');
        }

        $cabang->delete();

        return back()->with('success', 'Data cabang berhasil dihapus.');
    }

    private function validated(Request $request, ?Cabang $cabang = null): array
    {
        // Rapikan input: huruf besar, tanpa spasi di pinggir
        $request->merge([
            'kode_cabang'    => strtoupper(trim((string) $request->kode_cabang)),
            'kota_kabupaten' => strtoupper(trim((string) $request->kota_kabupaten)),
        ]);

        return $request->validate([
            'kode_cabang'    => ['required', 'regex:/^[A-Z0-9]{4}$/', Rule::unique('cabangs', 'kode_cabang')->ignore($cabang?->id)],
            'nama_cabang'    => ['required', 'string', 'max:100'],
            'alamat'         => ['required', 'string', 'max:255'],
            'kota_kabupaten' => ['required', 'string', 'max:50'],
            'provinsi'       => ['required', Rule::in(Cabang::PROVINSI)],
            'jenis_cabang'   => ['required', Rule::in(['INDUK', 'DEPO', 'SEWA'])],
            'region_id'      => ['required', 'exists:regions,id'],
            'latitude'       => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'      => ['nullable', 'numeric', 'between:-180,180'],
            'status'         => ['required', Rule::in(['AKTIF', 'TIDAK_AKTIF', 'TUTUP'])],
        ], [
            'kode_cabang.regex' => 'Kode cabang harus 4 karakter huruf atau angka, contoh: G259.',
        ]);
    }
}
