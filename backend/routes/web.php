<?php 

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\PeminjamController;

// Import Controller Admin yang sudah dipecah di dalam folder Admin
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CrudUser;
use App\Http\Controllers\Admin\CrudKategori;
use App\Http\Controllers\Admin\CrudAlat;
use App\Http\Controllers\Admin\CrudPeminjaman;
use App\Http\Controllers\Admin\CrudPengembalian;
use App\Http\Controllers\Admin\LogAktivitas;

// Redirect halaman utama
Route::get('/', function () {
    return redirect()->route('login');
});

// Route Guest
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Route Logout (Bisa diakses via GET & POST agar menahan error 419 saat tekan tombol Back)
Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');

// === ADMIN ROUTES (Dipecah sesuai modul fitur) ===
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // CRUD User (Menggunakan CrudUser)
    Route::get('/users', [CrudUser::class, 'index'])->name('user.index');
    Route::get('/users/create', [CrudUser::class, 'create'])->name('user.create');
    Route::post('/users', [CrudUser::class, 'store'])->name('user.store');
    Route::get('/users/{id}/edit', [CrudUser::class, 'edit'])->name('user.edit');
    Route::put('/users/{id}', [CrudUser::class, 'update'])->name('user.update');
    Route::delete('/users/{id}', [CrudUser::class, 'destroy'])->name('user.destroy');

    // CRUD Kategori (Menggunakan CrudKategori)
    Route::get('/kategori', [CrudKategori::class, 'index'])->name('kategori.index');
    Route::get('/kategori/create', [CrudKategori::class, 'create'])->name('kategori.create');
    Route::post('/kategori', [CrudKategori::class, 'store'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [CrudKategori::class, 'edit'])->name('kategori.edit');
    Route::put('/kategori/{id}', [CrudKategori::class, 'update'])->name('kategori.update');
    Route::delete('/kategori/{id}', [CrudKategori::class, 'destroy'])->name('kategori.destroy');

    // CRUD Alat (Menggunakan CrudAlat)
    Route::get('/alat', [CrudAlat::class, 'index'])->name('alat.index');
    Route::get('/alat/create', [CrudAlat::class, 'create'])->name('alat.create');
    Route::post('/alat', [CrudAlat::class, 'store'])->name('alat.store');
    Route::get('/alat/{id}/edit', [CrudAlat::class, 'edit'])->name('alat.edit');
    Route::put('/alat/{id}', [CrudAlat::class, 'update'])->name('alat.update');
    Route::delete('/alat/{id}', [CrudAlat::class, 'destroy'])->name('alat.destroy');

    // CRUD Peminjaman (Menggunakan CrudPeminjaman)
    Route::get('/peminjaman', [CrudPeminjaman::class, 'index'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [CrudPeminjaman::class, 'create'])->name('peminjaman.create');
    Route::post('/peminjaman', [CrudPeminjaman::class, 'store'])->name('peminjaman.store');
    Route::put('/peminjaman/{id}', [CrudPeminjaman::class, 'updateStatus'])->name('peminjaman.update');
    Route::delete('/peminjaman/{id}', [CrudPeminjaman::class, 'destroy'])->name('peminjaman.destroy');

    // MODUL KELOLA PENGEMBALIAN (Menggunakan CrudPengembalian)
    Route::get('/pengembalian', [CrudPengembalian::class, 'index'])->name('pengembalian.index');
    Route::post('/pengembalian/{id}', [CrudPengembalian::class, 'store'])->name('pengembalian.proses');

    // MODUL LOG AKTIVITAS (Menggunakan LogAktivitas)
    Route::get('/log-aktivitas', [LogAktivitas::class, 'index'])->name('log.index');
});

// === PETUGAS ROUTES ===
Route::middleware(['auth', 'role:petugas,admin'])->prefix('petugas')->name('petugas.')->group(function () {
    Route::get('/peminjaman', [PetugasController::class, 'indexPeminjaman'])->name('peminjaman.index');
    Route::post('/peminjaman/{id}/setujui', [PetugasController::class, 'setujuiPeminjaman'])->name('peminjaman.setujui');
    Route::post('/peminjaman/{id}/tolak', [PetugasController::class, 'tolakPeminjaman'])->name('peminjaman.tolak');

    Route::get('/pengembalian', [PetugasController::class, 'indexPengembalian'])->name('pengembalian.index');
    Route::post('/pengembalian/{id}', [PetugasController::class, 'prosesPengembalian'])->name('pengembalian.proses');

    // Cetak Laporan & Bon Struk
    Route::get('/laporan', [PetugasController::class, 'indexLaporan'])->name('laporan.index');
    Route::get('/laporan/nota/{id}', [PetugasController::class, 'cetakNota'])->name('laporan.nota');
});

// === PEMINJAM ROUTES ===
Route::middleware(['auth', 'role:peminjam'])->prefix('peminjam')->name('peminjam.')->group(function () {
    Route::get('/dashboard', [PeminjamController::class, 'dashboard'])->name('dashboard');
    
    // Fitur 1: Melihat Daftar Alat
    Route::get('/katalog', [PeminjamController::class, 'katalogAlat'])->name('katalog');
    
    // Fitur 2: Mengajukan Peminjaman
    Route::post('/peminjaman/ajukan', [PeminjamController::class, 'ajukanPeminjaman'])->name('peminjaman.ajukan');
    
    // Fitur 3: Mengembalikan Alat
    Route::get('/pengembalian', [PeminjamController::class, 'pengembalian'])->name('pengembalian');
    Route::post('/pengembalian/{id}', [PeminjamController::class, 'prosesPengembalian'])->name('pengembalian.proses');
    
    // Route Pendukung Sidebar
    Route::get('/riwayat', [PeminjamController::class, 'riwayatPeminjaman'])->name('riwayat');
    Route::get('/profil', [PeminjamController::class, 'profil'])->name('profil');
});