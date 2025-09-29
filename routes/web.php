<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\JurusanController;
use App\Http\Controllers\Admin\KelasController;
use App\Http\Controllers\Admin\RuanganController;
use App\Http\Controllers\Admin\MapelController;
use App\Http\Controllers\Admin\GuruController;
use App\Http\Controllers\Admin\GuruMapelController;
use App\Http\Controllers\Admin\PenjadwalanController;
use App\Http\Controllers\Admin\JadwalKhususController;
use App\Http\Controllers\Admin\KebutuhanMapelKelasController;
use App\Http\Controllers\Admin\HistoryJadwalController;
use App\Http\Controllers\Admin\ProfilAdminController;

use App\Http\Controllers\Guru\DashboardGuruController;
use App\Http\Controllers\Guru\JadwalGuruController;
use App\Http\Controllers\Guru\ProfilGuruController;
use App\Http\Controllers\Guru\JadwalKhususGuruController;
use App\Http\Controllers\Guru\ProfilGuruController as GuruProfilController;

use App\Http\Controllers\KepalaSekolah\DashboardKepalaSekolahController;
use App\Http\Controllers\KepalaSekolah\GuruMonitorController;
use App\Http\Controllers\KepalaSekolah\PenjadwalanMonitorController;
use App\Http\Controllers\KepalaSekolah\JadwalKhususMonitorController;
use App\Http\Controllers\KepalaSekolah\ProfilKepalaSekolahController;
use App\Http\Controllers\KepalaSekolah\ProfilKepalaSekolahController as KepalaSekolahProfilController;
use App\Http\Controllers\KepalaSekolah\CetakController;
use App\Http\Controllers\KepalaSekolah\JurusanMonitorController;
use App\Http\Controllers\KepalaSekolah\KelasMonitorController;
use App\Http\Controllers\KepalaSekolah\MapelMonitorController;

Route::get('/', function () {
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $u = Auth::user();

    return match ($u->role) {
        'admin'           => redirect()->route('admin.dashboard'),
        'guru'            => redirect()->route('guru.dashboard'),
        'kepala_sekolah'  => redirect()->route('kepala_sekolah.dashboard'),
        default           => abort(403, 'Role tidak dikenali'),
    };
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('password.request');
    Route::post('/reset-password', [AuthController::class, 'sendResetLink']);
});
Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');


// Admin
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    Route::get('/admin/jurusan', [JurusanController::class, 'index'])->name('admin.jurusan.index');
    Route::post('/admin/jurusan', [JurusanController::class, 'store'])->name('admin.jurusan.store');
    Route::put('/admin/jurusan/{id}', [JurusanController::class, 'update'])->name('admin.jurusan.update');
    Route::delete('/admin/jurusan/{id}', [JurusanController::class, 'destroy'])->name('admin.jurusan.destroy');

    Route::get('/admin/kelas', [KelasController::class, 'index'])->name('admin.kelas.index');
    Route::post('/admin/kelas', [KelasController::class, 'store'])->name('admin.kelas.store');
    Route::put('/admin/kelas/{id}', [KelasController::class, 'update'])->name('admin.kelas.update');
    Route::delete('/admin/kelas/{id}', [KelasController::class, 'destroy'])->name('admin.kelas.destroy');

    Route::get('/admin/ruangan', [RuanganController::class, 'index'])->name('admin.ruangan.index');
    Route::post('/admin/ruangan', [RuanganController::class, 'store'])->name('admin.ruangan.store');
    Route::put('/admin/ruangan/{id}', [RuanganController::class, 'update'])->name('admin.ruangan.update');
    Route::delete('/admin/ruangan/{id}', [RuanganController::class, 'destroy'])->name('admin.ruangan.destroy');

    Route::get('/admin/mapel', [MapelController::class, 'index'])->name('admin.mapel.index');
    Route::post('/admin/mapel', [MapelController::class, 'store'])->name('admin.mapel.store');
    Route::put('/admin/mapel/{id}', [MapelController::class, 'update'])->name('admin.mapel.update');
    Route::delete('/admin/mapel/{id}', [MapelController::class, 'destroy'])->name('admin.mapel.destroy');

    Route::get('/admin/guru', [GuruController::class, 'index'])->name('admin.guru.index');
    Route::post('/admin/guru', [GuruController::class, 'store'])->name('admin.guru.store');
    Route::put('/admin/guru/{id}', [GuruController::class, 'update'])->name('admin.guru.update');
    Route::post('/admin/guru/{id}/reset-password', [GuruController::class, 'resetPassword'])->name('admin.guru.resetPassword');
    Route::delete('/admin/guru/{id}', [GuruController::class, 'destroy'])->name('admin.guru.destroy');

    Route::get('/admin/guru-mata-pelajaran', [GuruMapelController::class, 'index'])->name('admin.guru-mapel.index');
    Route::post('/admin/guru-mata-pelajaran', [GuruMapelController::class, 'store'])->name('admin.guru-mapel.store');
    Route::put('/admin/guru-mata-pelajaran/{id}', [GuruMapelController::class, 'update'])->name('admin.guru-mapel.update');
    Route::delete('/admin/guru-mata-pelajaran/{id}', [GuruMapelController::class, 'destroy'])->name('admin.guru-mapel.destroy');

    Route::get('/admin/kebutuhan-mapel-kelas', [KebutuhanMapelKelasController::class, 'index'])->name('admin.kebutuhan-mapel-kelas.index');
    Route::post('/admin/kebutuhan-mapel-kelas', [KebutuhanMapelKelasController::class, 'store'])->name('admin.kebutuhan-mapel-kelas.store');
    Route::put('/admin/kebutuhan-mapel-kelas/{id}', [KebutuhanMapelKelasController::class, 'update'])->name('admin.kebutuhan-mapel-kelas.update');
    Route::delete('/admin/kebutuhan-mapel-kelas/{id}', [KebutuhanMapelKelasController::class, 'destroy'])->name('admin.kebutuhan-mapel-kelas.destroy');

    Route::get('/admin/penjadwalan', [PenjadwalanController::class, 'index'])->name('admin.penjadwalan.index');
    Route::post('/admin/penjadwalan', [PenjadwalanController::class, 'store'])->name('admin.penjadwalan.store');
    Route::put('/admin/penjadwalan/{id}', [PenjadwalanController::class, 'update'])->name('admin.penjadwalan.update');
    Route::delete('/admin/penjadwalan/{id}', [PenjadwalanController::class, 'destroy'])->name('admin.penjadwalan.destroy');

    Route::post('/admin/penjadwalan/reset/{kelas}', [PenjadwalanController::class, 'reset'])->name('admin.penjadwalan.reset');
    Route::post('/admin/penjadwalan/reset-all', [PenjadwalanController::class, 'resetAll'])->name('admin.penjadwalan.resetAll');
    Route::post('/admin/penjadwalan/generate', [PenjadwalanController::class, 'generate'])->name('admin.penjadwalan.generate'); // PREVIEW (tanpa DB)
    Route::post('/admin/penjadwalan/{kelas}/generate', [PenjadwalanController::class, 'generateKelas'])->name('admin.penjadwalan.generate_kelas'); // PREVIEW per kelas
    Route::post('/admin/penjadwalan/simpan', [PenjadwalanController::class, 'simpan'])->name('admin.penjadwalan.simpan'); // SIMPAN (ke DB)
    Route::post('/admin/penjadwalan/batal', [PenjadwalanController::class, 'cancelPreview'])->name('admin.penjadwalan.batal');

    Route::post('/admin/penjadwalan/jam0-all', [PenjadwalanController::class, 'isiJam0All'])->name('admin.penjadwalan.isiJam0All');
    Route::post('/admin/penjadwalan/ekskul-all', [PenjadwalanController::class, 'isiEkskulAll'])->name('admin.penjadwalan.isiEkskulAll');

    Route::get('/admin/jadwal_khusus', [JadwalKhususController::class, 'index'])->name('admin.jadwal_khusus.index');
    Route::post('/admin/jadwal_khusus', [JadwalKhususController::class, 'store'])->name('admin.jadwal_khusus.store');
    Route::put('/admin/jadwal-khusus/{id}', [JadwalKhususController::class, 'update'])->name('admin.jadwal_khusus.update');
    Route::delete('/admin/jadwal_khusus/{id}', [JadwalKhususController::class, 'destroy'])->name('admin.jadwal_khusus.destroy');

    Route::get('/admin/histori-jadwal', [HistoryJadwalController::class, 'index'])->name('admin.histori.index');
    Route::get('/admin/histori-jadwal/{batch}', [HistoryJadwalController::class, 'show'])->name('admin.histori.show');
    Route::get('/admin/histori-jadwal/{batch}/pdf', [HistoryJadwalController::class, 'exportPdf'])->name('admin.histori.pdf');

    Route::get('/admin/profil', [ProfilAdminController::class, 'index'])->name('admin.profil.index');
    Route::put('/admin/profil/update-password', [ProfilAdminController::class, 'updatePassword'])->name('admin.profil.update-password');
});

// Guru
Route::middleware(['auth', 'role:guru'])->group(function () {
    Route::get('/guru/dashboard', [DashboardGuruController::class, 'index'])->name('guru.dashboard');
    Route::get('/guru/jadwal', [JadwalGuruController::class, 'index'])->name('guru.jadwal.index');
    Route::get('/guru/profil', [ProfilGuruController::class, 'index'])->name('guru.profil.index');
    Route::get('/guru/jadwal_khusus', [JadwalKhususGuruController::class, 'index'])->name('guru.jadwal_khusus.index');

    // ✅ Tambahan untuk fitur ganti password
    Route::put('/guru/profil/update-password', [GuruProfilController::class, 'updatePassword'])->name('guru.profil.update-password');
});


// Kepala Sekolah
Route::middleware(['auth', 'role:kepala_sekolah'])->group(function () {
    Route::get('/kepala_sekolah/dashboard', [DashboardKepalaSekolahController::class, 'index'])->name('kepala_sekolah.dashboard');
    Route::get('/kepala_sekolah/guru', [GuruMonitorController::class, 'index'])->name('kepala_sekolah.guru.index');
    Route::get('/kepala_sekolah/penjadwalan', [PenjadwalanMonitorController::class, 'index'])->name('kepala_sekolah.penjadwalan.index');
    Route::get('/kepala_sekolah/jadwal_khusus', [JadwalKhususMonitorController::class, 'index'])->name('kepala_sekolah.jadwal_khusus.index');
    Route::get('/kepala_sekolah/profil', [ProfilKepalaSekolahController::class, 'index'])->name('kepala_sekolah.profil.index');
    Route::put('/kepala-sekolah/profil/update-password', [KepalaSekolahProfilController::class, 'updatePassword'])->name('kepala_sekolah.profil.update-password');
    Route::get('/kepala_sekolah/cetak-jadwal', [CetakController::class, 'index'])->name('kepala_sekolah.cetak.index');
    Route::get('/kepala_sekolah/lihat-pdf', [CetakController::class, 'exportPdf'])->name('kepala_sekolah.cetak.pdf');
    Route::get('/kepala_sekolah/kelas', [KelasMonitorController::class, 'index'])->name('kepala_sekolah.kelas.index');
    Route::get('/kepala_sekolah/jurusan', [JurusanMonitorController::class, 'index'])->name('kepala_sekolah.jurusan.index');
    Route::get('/kepala_sekolah/mapel', [MapelMonitorController::class, 'index'])->name('kepala_sekolah.mapel.index');
});
