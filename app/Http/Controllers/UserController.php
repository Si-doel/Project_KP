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
            'cabangs' => Cabang::where('status', 'AKTIF')->orderBy('nama_cabang')->get(),
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
            'cabangs' => Cabang::where('status', 'AKTIF')->orderBy('nama_cabang')->get(),
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
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'nik'         => ['required', 'string', 'max:20', 'regex:/^[0-9]+$/', Rule::unique('users', 'nik')->ignore($user?->id)],
            'email'       => ['required', 'email', 'max:100', Rule::unique('users', 'email')->ignore($user?->id)],
            'password'    => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'role'        => ['required', Rule::in(['ADMIN', 'CABANG'])],
            'cabang_id'   => ['nullable', 'required_if:role,CABANG', 'exists:cabangs,id'],
            'status_akun' => ['required', Rule::in(['AKTIF', 'NONAKTIF'])],
        ]);

        if ($data['role'] === 'ADMIN') {
            $data['cabang_id'] = null;
        }

        return $data;
    }
}
