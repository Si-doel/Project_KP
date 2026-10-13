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
        return view('regions.form', ['region' => new Region()]);
    }

    public function store(Request $request)
    {
        Region::create($this->validated($request));

        return redirect()->route('regions.index')
            ->with('success', 'Data region berhasil ditambahkan.');
    }

    public function edit(Region $region)
    {
        return view('regions.form', compact('region'));
    }

    public function update(Request $request, Region $region)
    {
        $region->update($this->validated($request, $region));

        return redirect()->route('regions.index')
            ->with('success', 'Data region berhasil diperbarui.');
    }

    public function destroy(Region $region)
    {
        if ($region->cabangs()->exists()) {
            return back()->with('error', 'Region masih memiliki cabang. Pindahkan atau hapus cabangnya dulu.');
        }

        $region->delete();

        return back()->with('success', 'Data region berhasil dihapus.');
    }

    private function validated(Request $request, ?Region $region = null): array
    {
        // Rapikan input: huruf besar, tanpa spasi di pinggir
        $request->merge(['nama_region' => strtoupper(trim((string) $request->nama_region))]);

        return $request->validate([
            'nama_region'     => ['required', 'regex:/^REG-\d{2}$/', Rule::unique('regions', 'nama_region')->ignore($region?->id)],
            'nama_pejabat'    => ['nullable', 'string', 'max:100'],
            'jabatan_pejabat' => ['nullable', Rule::in(Region::JABATAN)],
        ], [
            'nama_region.regex' => 'Format region harus REG-XX, contoh: REG-01.',
        ]);
    }

    // private function validated(Request $request, ?Region $region = null): array
    // {
    //     return $request->validate([
    //         'nama_region'     => ['required', 'string', 'max:50', Rule::unique('regions', 'nama_region')->ignore($region?->id)],
    //         'nama_pejabat'    => ['nullable', 'string', 'max:100'],
    //         'jabatan_pejabat' => ['nullable', 'string', 'max:50'],
    //     ]);
    // }
}
