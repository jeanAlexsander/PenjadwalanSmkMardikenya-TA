<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        // Cari user dengan username case-sensitive
        $user = User::whereRaw('BINARY `username` = ?', [$credentials['username']])->first();

        // Cek apakah user ditemukan dan password cocok
        if ($user && Hash::check($credentials['password'], $user->password)) {
            Auth::login($user);
            $request->session()->regenerate();

            $nama = $user->guru?->name ?? 'Pengguna';

            if ($user->role === 'admin') {
                $message = 'Selamat datang, Admin!';
                return redirect()->route('admin.dashboard')->with('toast_success', $message);
            } elseif ($user->role === 'kepala_sekolah') {
                $message = 'Selamat datang, Kepala Sekolah ' . $nama . '!';
                return redirect()->route('kepala_sekolah.dashboard')->with('toast_success', $message);
            } elseif ($user->role === 'guru') {
                $message = 'Selamat datang, ' . $nama . '!';
                return redirect()->route('guru.dashboard')->with('toast_success', $message);
            } else {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'username' => 'Role pengguna tidak dikenali. Silakan hubungi admin.',
                ]);
            }
        }

        // Jika gagal login
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function showResetForm()
    {
        return view('auth.reset');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Sementara hanya menampilkan pesan, tanpa kirim email
        return back()->with('status', 'Link reset password telah dikirim ke email (simulasi).');
    }

    public function username()
    {
        return 'username';
    }
}
