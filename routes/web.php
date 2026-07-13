<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Pengaturan\AksesController;
use App\Http\Controllers\Pengaturan\AkunController;
use App\Http\Controllers\Pengaturan\AuditLogController;
use App\Http\Controllers\Pengaturan\MenuController;
use App\Http\Controllers\Pengaturan\UserController;
use Illuminate\Support\Facades\Route;

// ── AUTH (guest only) ─────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login',            [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',           [AuthController::class, 'login'])->name('login.post');
    Route::get('/lupa-password',    [AuthController::class, 'showForgotPassword'])->name('forgot-password');
    Route::post('/lupa-password',   [AuthController::class, 'forgotPassword'])->name('forgot-password.post');
    Route::get('/reset-password/{token}',  [AuthController::class, 'showResetPassword'])->name('reset-password');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('reset-password.post');
});

// ── AUTHENTICATED ────────────────────────────────────────
Route::middleware('auth.simak')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ── PENGATURAN ────────────────────────────────────────
    Route::prefix('pengaturan')->name('pengaturan.')->group(function () {

        // Akun sendiri
        Route::get('/akun', [AkunController::class, 'index'])->name('akun');
        Route::post('/akun/profil',   [AkunController::class, 'updateProfil'])->name('akun.profil');
        Route::post('/akun/password', [AkunController::class, 'gantiPassword'])->name('akun.password');

        // User (CRUD)
        Route::middleware('permission:pengaturan.user')->group(function () {
            Route::get('/user',            [UserController::class, 'index'])->name('user.index');
            Route::get('/user/{id}',       [UserController::class, 'show'])->name('user.show');
        });
        Route::middleware('permission:pengaturan.user,3')->group(function () {
            Route::post('/user',           [UserController::class, 'store'])->name('user.store');
            Route::put('/user/{id}',       [UserController::class, 'update'])->name('user.update');
            Route::patch('/user/{id}/toggle-active', [UserController::class, 'toggleActive'])->name('user.toggle');
            Route::patch('/user/{id}/reset-lock',    [UserController::class, 'resetLock'])->name('user.reset-lock');
        });
        Route::middleware('permission:pengaturan.user,7')->group(function () {
            Route::delete('/user/{id}',    [UserController::class, 'destroy'])->name('user.destroy');
        });

        // Menu
        Route::middleware('permission:pengaturan.menu')->group(function () {
            Route::get('/menu',        [MenuController::class, 'index'])->name('menu.index');
            Route::get('/menu/{id}',   [MenuController::class, 'show'])->name('menu.show');
        });
        Route::middleware('permission:pengaturan.menu,3')->group(function () {
            Route::post('/menu',       [MenuController::class, 'store'])->name('menu.store');
            Route::put('/menu/{id}',   [MenuController::class, 'update'])->name('menu.update');
            Route::patch('/menu/{id}/toggle', [MenuController::class, 'toggle'])->name('menu.toggle');
        });
        Route::middleware('permission:pengaturan.menu,7')->group(function () {
            Route::delete('/menu/{id}', [MenuController::class, 'destroy'])->name('menu.destroy');
        });

        // Akses / Permission
        Route::middleware('permission:pengaturan.akses')->group(function () {
            Route::get('/akses',                       [AksesController::class, 'index'])->name('akses.index');
            Route::get('/akses/role/{roleId}',         [AksesController::class, 'getPermissions'])->name('akses.get');
        });
        Route::middleware('permission:pengaturan.akses,3')->group(function () {
            Route::post('/akses/role/{roleId}',        [AksesController::class, 'savePermissions'])->name('akses.save');
        });

        // Audit Log
        Route::middleware('permission:pengaturan.audit_log')->group(function () {
            Route::get('/audit-log',      [AuditLogController::class, 'index'])->name('audit-log.index');
            Route::get('/audit-log/{id}', [AuditLogController::class, 'show'])->name('audit-log.show');
        });
    });
});
