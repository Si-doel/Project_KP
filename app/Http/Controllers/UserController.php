<?php

namespace App\Http\Controllers;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('cabang')
            ->when($request->q, function ($query, $q) {
                $query->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.form', [
            'user'    => new User(),
            'cabangs' => Cabang::where('status', 'AKTIF')
                ->orderBy('kode_cabang')
                ->get(),
            // 'cabangs' => Cabang::where('status', 'AKTIF')->orderBy('nama_cabang')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('users.index')
            ->with('success', 'Akun user berhasil dibuat.');
    }

    public function edit(User $user)
    {
        return view('users.form', [
            'user'    => $user,
            'cabangs' => Cabang::where('status', 'AKTIF')
                ->orderBy('kode_cabang')
                ->get(),
            // 'cabangs' => Cabang::where('status', 'AKTIF')->orderBy('nama_cabang')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Admin tidak terikat ke cabang; jangan sampai admin menonaktifkan akunnya sendiri
        // di method update()
        if ($user->id === Auth::id()) {
            $data['status_akun'] = 'AKTIF';
            $data['role'] = $user->role;
        }

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }
        $user->delete();

        return back()->with('success', 'Akun user berhasil dihapus.');
    }


    private function validated(Request $request, ?User $user = null): array
    {
        // Normalisasi input
        $request->merge([
            'name' => trim((string) $request->input('name', '')),
            'nik' => trim((string) $request->input('nik', '')),
            'email' => strtolower(trim((string) $request->input('email', ''))),
        ]);

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
            ],

            'nik' => [
                'required',
                'string',
                'max:20',
                'regex:/^[0-9]+$/',
                Rule::unique('users', 'nik')->ignore($user?->id),
            ],

            'email' => [
                'required',
                'email',
                'max:100',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],

            'password' => [
                $user ? 'nullable' : 'required',
                'confirmed',
                Password::defaults(),
            ],

            'role' => [
                'required',
                Rule::in(['ADMIN', 'CABANG']),
            ],

            'cabang_id' => [
                'nullable',
                'required_if:role,CABANG',
                'integer',
                'exists:cabangs,id',
            ],

            'status_akun' => [
                'required',
                Rule::in(['AKTIF', 'NONAKTIF']),
            ],

        ], [
            'name.required' => 'Nama user wajib diisi.',
            'name.max' => 'Nama user maksimal 100 karakter.',

            'nik.required' => 'NIK wajib diisi.',
            'nik.regex' => 'NIK hanya boleh berisi angka.',
            'nik.max' => 'NIK maksimal 20 karakter.',
            'nik.unique' => 'NIK tersebut sudah terdaftar.',

            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email maksimal 100 karakter.',
            'email.unique' => 'Email tersebut sudah digunakan.',

            'password.required' => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',

            'role.required' => 'Role wajib dipilih.',
            'role.in' => 'Role yang dipilih tidak valid.',

            'cabang_id.required_if' => 'Cabang wajib dipilih untuk role CABANG.',
            'cabang_id.integer' => 'Cabang yang dipilih tidak valid.',
            'cabang_id.exists' => 'Cabang yang dipilih tidak ditemukan.',

            'status_akun.required' => 'Status akun wajib dipilih.',
            'status_akun.in' => 'Status akun yang dipilih tidak valid.',
        ]);

        // Jika role ADMIN, akun tidak terikat ke cabang
        if ($data['role'] === 'ADMIN') {
            $data['cabang_id'] = null;
        }

        return $data;
    }
}
