<?php

namespace App\Http\Controllers\KepalaSekolah;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ProfilKepalaSekolahController extends Controller
{
    public function index()
    {
        $user = User::find(Auth::id());

        if ($user && $user->guru) {
            $user->load([
                'guru.guruMapel.mataPelajaran',
                'guru.guruMapel.jadwalPelajaran.kelas', // tambahkan ini
            ]);

            // Ambil semua kelas unik dari jadwal yang diampu guru
            $kelasDiampu = collect();

            foreach ($user->guru->guruMapel as $gm) {
                foreach ($gm->jadwalPelajaran as $jadwal) {
                    if ($jadwal->kelas) {
                        $kelasDiampu->push($jadwal->kelas);
                    }
                }
            }

            // Hilangkan duplikat berdasarkan ID kelas
            $kelasDiampu = $kelasDiampu->unique('id')->values();
        } else {
            $kelasDiampu = collect(); // kosongkan jika tidak ada
        }

        return view('kepala_sekolah.profil.index', compact('user', 'kelasDiampu'));
    }

    public function updatePassword(Request $request)
    {
        Log::info('Masuk method updatePassword');

        // 1) Validasi manual → bisa kirim toast_error yang rapi
        $validator = Validator::make($request->all(), [
            'old_password' => ['required'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed'], // butuh field new_password_confirmation
        ], [
            'old_password.required'  => 'Password lama wajib diisi.',
            'new_password.required'  => 'Password baru wajib diisi.',
            'new_password.min'       => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak sesuai.',
        ]);

        if ($validator->fails()) {
            Log::warning('Validasi gagal update password', ['errors' => $validator->errors()->toArray()]);
            return back()->withInput()->with('toast_error', $validator->errors()->first());
        }

        // 2) Ambil user
        $user = User::find(Auth::id());
        if (!$user) {
            Log::error('User tidak ditemukan', ['id' => Auth::id()]);
            return back()->with('toast_error', 'Akun tidak ditemukan.');
        }

        // (Opsional) Tolak jika password baru sama dengan lama
        if (Hash::check($request->new_password, $user->password)) {
            return back()->withInput()->with('toast_error', 'Password baru tidak boleh sama dengan password lama.');
        }

        // 3) Cek password lama
        if (!Hash::check($request->old_password, $user->password)) {
            Log::warning('Password lama salah', ['user_id' => $user->id]);
            return back()->with('toast_error', 'Password lama tidak sesuai.');
        }

        // 4) Update password (try/catch hanya untuk proses update)
        Log::info('Sebelum update password', ['user_id' => $user->id]);
        try {
            $user->forceFill(['password' => Hash::make($request->new_password)])->save();
        } catch (\Throwable $e) {
            Log::error('Gagal update password', ['user_id' => $user->id, 'err' => $e->getMessage()]);
            return back()->withInput()->with('toast_error', 'Terjadi kesalahan saat mengganti password.');
        }

        Log::info('Password user diperbarui', ['user_id' => $user->id]); // jangan log hash
        return back()->with('toast_success', 'Password berhasil diganti.');
    }
}
