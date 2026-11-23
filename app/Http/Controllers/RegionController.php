<?php

namespace App\Http\Controllers;

use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RegionController extends Controller
{
    public function index(Request $request)
    {
        $regions = Region::withCount('cabangs')
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('nama_region', 'like', "%{$q}%")
                        ->orWhere('nama_pejabat', 'like', "%{$q}%");
                });
            })
            ->orderBy('nama_region')
            ->paginate(15)
            ->withQueryString();

        return view('regions.index', compact('regions'));
    }

    public function create()
    {
        return view('regions.form', [
            'region' => new Region()
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        Region::create($data);

        return redirect()->route('regions.index')
            ->with('success', 'Data region berhasil ditambahkan.');
    }

    public function edit(Region $region)
    {
        return view('regions.form', compact('region'));
    }

    public function update(Request $request, Region $region)
    {
        $data = $this->validated($request, $region);

        $region->update($data);

        return redirect()->route('regions.index')
            ->with('success', 'Data region berhasil diperbarui.');
    }

    public function destroy(Region $region)
    {
        if ($region->cabangs()->exists()) {
            return back()->with(
                'error',
                'Region masih memiliki cabang. Pindahkan atau hapus cabangnya dulu.'
            );
        }

        $region->delete();

        return back()->with(
            'success',
            'Data region berhasil dihapus.'
        );
    }

    private function validated(
        Request $request,
        ?Region $region = null
    ): array {
        $request->merge([
            'nama_region' => strtoupper(trim((string) $request->input('nama_region', ''))),
            'nama_pejabat' => trim((string) $request->input('nama_pejabat', '')),
            'jabatan_pejabat' => trim((string) $request->input('jabatan_pejabat', '')),
        ]);

        return $request->validate([
            'nama_region' => [
                'required',
                'string',
                'max:6',
                'regex:/^REG-\d{2}$/',
                Rule::unique('regions', 'nama_region')
                    ->ignore($region?->getKey()),
            ],

            'nama_pejabat' => [
                'required',
                'string',
                'max:100',
            ],

            'jabatan_pejabat' => [
                'required',
                Rule::in(Region::JABATAN),
            ],
        ], [
            'nama_region.required' => 'Nama region wajib diisi.',
            'nama_region.regex' => 'Format region harus REG-XX, contoh: REG-01.',
            'nama_region.unique' => 'Nama region tersebut sudah terdaftar.',

            'nama_pejabat.required' => 'Nama pejabat wajib diisi.',
            'nama_pejabat.string' => 'Nama pejabat harus berupa teks.',
            'nama_pejabat.max' => 'Nama pejabat maksimal 100 karakter.',

            'jabatan_pejabat.required' => 'Jabatan pejabat wajib dipilih.',
            'jabatan_pejabat.in' => 'Jabatan pejabat yang dipilih tidak valid.',
        ]);
    }
}
