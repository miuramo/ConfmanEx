<?php

use App\Http\Controllers\RegistController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::resource('regist', RegistController::class);
    // override edit
    Route::get('/regist/{regist}/edit/{token?}', [RegistController::class, 'edit'])->name('regist.edit');
    Route::get('/regist/{regist}/show/{token?}', [RegistController::class, 'show'])->name('regist.show');

    Route::get('/regist_email/{regist}', [RegistController::class, 'email'])->name('regist.email');

    Route::get('/regist_sponsor/{token}', [RegistController::class, 'create_for_sponsors'])->name('regist.sponsor');
    Route::get('/regist_admin/{user_id}', [RegistController::class, 'create_for_admin'])->name('regist.admin');
});

// Route::middleware('guest')->group(function () {});

Route::get('/regist_entry', [RegistController::class, 'entry'])->name('regist.entry');
