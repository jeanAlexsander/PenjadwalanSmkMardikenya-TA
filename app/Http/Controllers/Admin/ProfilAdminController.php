<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ProfilAdminController extends Controller
{

    /**
     * Tampilkan halaman profil admin (hanya info + ganti password).
     */
    public function index()
    {
        $user = Auth::user(); // admin yang sedang login
        return view('admin.profil.index', compact('user'));
    }

    /**
     * Ganti password admin sendiri.
     */
    public function updatePassword(Request $request)
    {
        Log::info('Admin::updatePassword masuk', ['admin_id' => Auth::id()]);

        // 1) Validasi manual supaya bisa kirim toast_error yang rapi
        $validator = Validator::make($request->all(), [
            'old_password' => ['required'],
            'new_password' => ['required', 'string', 'min:5', 'confirmed'], // perlu field new_password_confirmation
        ], [
            'old_password.required'   => 'Password lama wajib diisi.',
            'new_password.required'   => 'Password baru wajib diisi.',
            'new_password.min'        => 'Password baru minimal 5 karakter.',
            'new_password.confirmed'  => 'Konfirmasi password baru tidak sesuai.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validasi gagal update password admin', ['errors' => $validator->errors()->toArray()]);
            return back()->withInput()->with('toast_error', $validator->errors()->first());
        }

        // 2) Ambil user (admin yang sedang login)
        $user = User::find(Auth::id());
        if (!$user) {
            Log::error('User tidak ditemukan', ['id' => Auth::id()]);
            return back()->with('toast_error', 'Akun tidak ditemukan.');
        }

        // 3) Cek password lama
        if (!Hash::check($request->old_password, $user->password)) {
            Log::warning('Password lama tidak sesuai', ['user_id' => $user->id]);
            return back()->with('toast_error', 'Password lama tidak sesuai.');
        }

        // 4) Update password
        try {
            $user->forceFill(['password' => Hash::make($request->new_password)])->save();
        } catch (\Throwable $e) {
            Log::error('Gagal update password admin', ['user_id' => $user->id, 'err' => $e->getMessage()]);
            return back()->withInput()->with('toast_error', 'Terjadi kesalahan saat mengganti password.');
        }

        Log::info('Password admin diperbarui', ['user_id' => $user->id]); // jangan log hash
        return back()->with('toast_success', 'Password berhasil diganti.');
    }
}
