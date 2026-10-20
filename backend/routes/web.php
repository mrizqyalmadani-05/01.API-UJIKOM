<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeminjamController;
use App\Http\Controllers\PetugasController;
use Illuminate\Support\Facades\Route;

// Redirect halaman utama ke login
Route::get('/', function () {
    return redirect()->route('login');
});

// Route Tamu (Belum Login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Route Logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// =========================================================
// Admin
// =========================================================
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');

    // Log Aktivitas
    Route::get('/log', [AdminController::class, 'logAktivitas'])->name('log.index');

    // Laporan
    Route::get('/laporan', [AdminController::class, 'laporan'])->name('laporan.index');
    Route::get('/laporan/cetak', [AdminController::class, 'cetakLaporan'])->name('laporan.cetak');
    Route::get('/laporan/excel', [AdminController::class, 'excelLaporan'])->name('laporan.excel'); // fitur tambahan: export Excel

    // CRUD Alat
    Route::get('/alat', [AdminController::class, 'indexAlat'])->name('alat.index');
    Route::get('/alat/create', [AdminController::class, 'createAlat'])->name('alat.create');
    Route::post('/alat', [AdminController::class, 'storeAlat'])->name('alat.store');
    Route::get('/alat/{id}/edit', [AdminController::class, 'editAlat'])->name('alat.edit');
    Route::put('/alat/{id}', [AdminController::class, 'updateAlat'])->name('alat.update');
    Route::delete('/alat/{id}', [AdminController::class, 'destroyAlat'])->name('alat.destroy');

    // CRUD Kategori
    Route::get('/kategori', [AdminController::class, 'indexKategori'])->name('kategori.index');
    Route::get('/kategori/create', [AdminController::class, 'createKategori'])->name('kategori.create');
    Route::post('/kategori', [AdminController::class, 'storeKategori'])->name('kategori.store');
    Route::get('/kategori/{id}/edit', [AdminController::class, 'editKategori'])->name('kategori.edit');
    Route::put('/kategori/{id}', [AdminController::class, 'updateKategori'])->name('kategori.update');
    Route::delete('/kategori/{id}', [AdminController::class, 'destroyKategori'])->name('kategori.destroy');

    // CRUD User
    Route::get('/users', [AdminController::class, 'indexUser'])->name('user.index');
    Route::get('/users/create', [AdminController::class, 'createUser'])->name('user.create');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('user.store');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('user.edit');
    Route::put('/users/{id}', [AdminController::class, 'updateUser'])->name('user.update');
    Route::delete('/users/{id}', [AdminController::class, 'destroyUser'])->name('user.destroy');

    // CRUD Peminjaman
    Route::get('/peminjaman', [AdminController::class, 'indexPeminjaman'])->name('peminjaman.index');
    Route::get('/peminjaman/create', [AdminController::class, 'createPeminjaman'])->name('peminjaman.create');
    Route::post('/peminjaman', [AdminController::class, 'storePeminjaman'])->name('peminjaman.store');
    Route::put('/peminjaman/{id}/status', [AdminController::class, 'updateStatusPeminjaman'])->name('peminjaman.updateStatus');
    Route::delete('/peminjaman/{id}', [AdminController::class, 'destroyPeminjaman'])->name('peminjaman.destroy');

    // CRUD Pengembalian
    Route::get('/pengembalian', [AdminController::class, 'indexPengembalian'])->name('pengembalian.index');
    Route::get('/pengembalian/create', [AdminController::class, 'createPengembalian'])->name('pengembalian.create');
    Route::post('/pengembalian', [AdminController::class, 'storePengembalian'])->name('pengembalian.store');
    Route::get('/pengembalian/{id}', [AdminController::class, 'showPengembalian'])->name('pengembalian.show');
    Route::delete('/pengembalian/{id}', [AdminController::class, 'destroyPengembalian'])->name('pengembalian.destroy');
});

// =========================================================
// Petugas
// =========================================================
Route::middleware(['auth', 'role:petugas,admin'])->prefix('petugas')->name('petugas.')->group(function () {

    // Kelola Peminjaman
    Route::get('/peminjaman', [PetugasController::class, 'indexPeminjaman'])->name('peminjaman.index');
    Route::post('/peminjaman/{id}/setujui', [PetugasController::class, 'setujuiPeminjaman'])->name('peminjaman.setujui');
    Route::post('/peminjaman/{id}/tolak', [PetugasController::class, 'tolakPeminjaman'])->name('peminjaman.tolak');

    // Memantau & Proses Pengembalian
    Route::get('/pengembalian', [PetugasController::class, 'memantauPengembalian'])->name('pengembalian.monitor');
    Route::post('/pengembalian/{id}/proses', [PetugasController::class, 'prosesPengembalian'])->name('pengembalian.proses');

    // Laporan
    Route::get('/laporan', [PetugasController::class, 'laporanIndex'])->name('laporan.index');
    Route::get('/laporan/cetak', [PetugasController::class, 'cetakLaporan'])->name('laporan.cetak');
    Route::get('/laporan/excel', [PetugasController::class, 'excelLaporan'])->name('laporan.excel'); // fitur tambahan: export Excel
});

// =========================================================
// Peminjam
// =========================================================
Route::middleware(['auth', 'role:peminjam'])->prefix('peminjam')->name('peminjam.')->group(function () {

    // Katalog & Pengajuan
    Route::get('/katalog', [PeminjamController::class, 'katalogAlat'])->name('katalog');
    Route::post('/peminjaman/ajukan', [PeminjamController::class, 'ajukanPeminjaman'])->name('peminjaman.ajukan');

    // Riwayat
    Route::get('/riwayat', [PeminjamController::class, 'riwayatPeminjaman'])->name('riwayat');

    // Pengembalian Alat
    Route::get('/kembalikan/{peminjaman_id}', [PeminjamController::class, 'formKembalikan'])->name('kembalikan.form');
    Route::post('/kembalikan/{peminjaman_id}', [PeminjamController::class, 'ajukanPengembalian'])->name('kembalikan.ajukan');

    // Profil Peminjam (info akun, menu profil, foto profil)
    Route::get('/profil', [PeminjamController::class, 'profil'])->name('profil');
    Route::get('/profil/foto', [PeminjamController::class, 'fotoProfil'])->name('profil.foto');
    Route::put('/profil', [PeminjamController::class, 'updateProfil'])->name('profil.update');
    Route::post('/profil/foto/hapus', [PeminjamController::class, 'hapusFotoProfil'])->name('profil.foto.hapus');

    // Fitur tambahan: peminjam meminta petugas memproses pengembalian
    // (status 'dipinjam' -> 'pengajuan_kembali')
    Route::post('/kembalikan/{peminjaman_id}/ajukan-kembali', [PeminjamController::class, 'ajukanKembali'])->name('kembalikan.ajukan-kembali');
});
