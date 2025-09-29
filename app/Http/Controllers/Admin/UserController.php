<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Guru;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('guru')->get();
        return view('admin.user.index', compact('users'));
    }

    public function create()
    {
        return view('admin.user.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => 'required|in:admin,guru,kepala_sekolah',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        // Jika role guru, tambahkan ke tabel guru
        if ($request->role === 'guru') {
            Guru::create([
                'user_id' => $user->id,
                'nama' => $user->name,
                'nip' => null, // kosong dulu atau sesuaikan
                'jenis_kelamin' => null,
                'no_hp' => null,
            ]);
        }

        return redirect()->route('admin.user.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Jika guru, hapus juga entri di tabel guru
        if ($user->role === 'guru' && $user->guru) {
            $user->guru()->delete();
        }

        $user->delete();

        return redirect()->route('admin.user.index')->with('success', 'User berhasil dihapus.');
    }
}
