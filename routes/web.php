<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Dashboard Routes (Protected)
Route::middleware(['auth'])->group(function () {
    
    // Redirect / ke dashboard admin
    Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard']);

    // Verifikasi User
    Route::get('/verifikasi-user', [AdminController::class, 'verifikasiUser'])->name('verifikasi.user');
    Route::post('/verifikasi-user/{user}/verify', [AdminController::class, 'verifyUser'])->name('verifikasi.user.verify');
    Route::post('/verifikasi-user/{user}/reject', [AdminController::class, 'rejectUser'])->name('verifikasi.user.reject');
    Route::post('/admin/user/{user}/unverify', [AdminController::class, 'unverifyUser'])->name('admin.user.unverify');
    Route::put('/admin/user/{user}', [AdminController::class, 'updateUser'])->name('admin.user.update');
    Route::delete('/admin/user/{user}', [AdminController::class, 'destroyUser'])->name('admin.user.destroy');

    // Manajemen POS Jaga
    Route::get('/tambah-pos-jaga', [AdminController::class, 'tambahPosJaga'])->name('tambah.pos');
    Route::post('/tambah-pos-jaga', [AdminController::class, 'tambahPosJaga'])->name('tambah.pos.store');
    Route::get('/pos-jaga/{pos}/edit', [AdminController::class, 'editPosJaga'])->name('tambah.pos.edit');
    Route::put('/pos-jaga/{pos}', [AdminController::class, 'updatePosJaga'])->name('tambah.pos.update');
    Route::delete('/pos-jaga/{pos}', [AdminController::class, 'destroyPosJaga'])->name('tambah.pos.destroy');

    // Pengaturan Jam Kerja
    Route::get('/edit-jam-kerja', [AdminController::class, 'editJamKerja'])->name('edit.jam');
    Route::post('/edit-jam-kerja', [AdminController::class, 'editJamKerja'])->name('edit.jam.save');

    // Laporan Kejadian
    Route::get('/admin/laporan-kejadian', [AdminController::class, 'laporanKejadian'])->name('admin.laporan');
    Route::get('/admin/laporan-kejadian/print', [AdminController::class, 'printLaporan'])->name('admin.laporan.print');
    Route::delete('/admin/laporan-kejadian/{l}', [AdminController::class, 'deleteLaporan'])->name('admin.laporan.delete');

    // Ekspor Data
    Route::get('/ekspor-laporan', [AdminController::class, 'eksporLaporan'])->name('ekspor.laporan');

    // Ringkasan Sistem
    Route::get('/ringkasan-sistem', [AdminController::class, 'ringkasanSistem'])->name('ringkasan.sistem');

    // Notifikasi
    Route::post('/admin/notifications/read', [AdminController::class, 'markNotificationsRead'])->name('admin.notifications.read');

    // Reset Absensi
    Route::delete('/admin/absensi/{attendance}', [AdminController::class, 'destroyAbsensi'])->name('admin.absensi.destroy');
});
