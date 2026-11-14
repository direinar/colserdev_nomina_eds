<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestión de usuarios: solo los roles administrativos.
    Route::middleware('role:'.implode(',', User::rolesAdministrativos()))->group(function () {
        Route::resource('users', UserController::class);
    });
});

require __DIR__.'/settings.php';
