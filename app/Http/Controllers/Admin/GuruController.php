<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class GuruController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 10);

        $guru = Guru::with('user')
            ->orderBy('id', 'desc')          // ganti kalau mau urut lain
            ->paginate($perPage)
            ->withQueryString();             // bawa query saat pindah halaman

        return view('admin.guru.index', compact('guru'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name'           => 'required|string|max:255',
                'email'          => 'required|email|unique:gurus,email',
                'nip'            => 'required|string|max:50|unique:gurus,nip',
                'jenis_kelamin'  => 'required|in:L,P',
                'alamat'         => 'nullable|string',
            ], [
                'email.unique' => 'Email sudah terdaftar untuk guru lain.',
                'nip.unique'   => 'NIP sudah terdaftar untuk guru lain.',
            ]);

            DB::beginTransaction();

            $user = User::create([
                'username' => $request->nip ?? $request->email,
                'password' => Hash::make('guru123'),
                'role'     => 'guru',
            ]);

            Guru::create([
                'user_id'        => $user->id,
                'nip'            => $request->nip,
                'name'           => $request->name,
                'email'          => $request->email,
                'jenis_kelamin'  => $request->jenis_kelamin,
                'alamat'         => $request->alamat,
            ]);

            DB::commit();

            return redirect()->route('admin.guru.index')->with('toast_success', 'Guru berhasil ditambahkan.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('tampilModalTambahGuru', true);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('toast_error', 'Terjadi kesalahan saat menambahkan guru.');
        }
    }

    public function update(Request $request, $id)
    {
        $guru = Guru::with('user')->findOrFail($id);

        if (!$guru->user) {
            return back()->with('toast_error', 'User tidak ditemukan.');
        }

        try {
            $request->validate([
                'name'   => 'required|string|max:255',
                'email'  => 'required|email|unique:gurus,email,' . $guru->id,
                'alamat' => 'nullable|string',
            ], [
                'email.unique' => 'Email sudah terdaftar untuk guru lain.',
            ]);

            DB::beginTransaction();

            $guru->update([
                'name'   => $request->name,
                'email'  => $request->email,
                'alamat' => $request->alamat,
            ]);

            DB::commit();

            return redirect()->route('admin.guru.index')->with('toast_success', 'Data guru berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('tampilModalEditGuru', true)
                ->with('editGuruId', $id);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('toast_error', 'Terjadi kesalahan saat memperbarui guru.')
                ->with('tampilModalEditGuru', true)
                ->with('editGuruId', $id)
                ->withInput();
        }
    }


    public function destroy($id)
    {
        $guru = Guru::with('user')->findOrFail($id);

        DB::beginTransaction();

        try {
            if ($guru->user) {
                $guru->user->delete();
            }

            $guru->delete();

            DB::commit();

            return redirect()->route('admin.guru.index')->with('toast_success', 'Guru berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('toast_error', 'Terjadi kesalahan saat menghapus guru.');
        }
    }

    public function resetPassword($id)
    {
        $guru = Guru::findOrFail($id);
        $user = User::findOrFail($guru->user_id);

        $user->password = Hash::make('guru123');
        $user->save();

        return back()->with('toast_success', 'Password berhasil direset.');
    }
}
