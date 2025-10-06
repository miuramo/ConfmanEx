<?php

use App\Http\Controllers\RegistController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::resource('regist', RegistController::class);
    Route::get('/regist_email/{regist}', [RegistController::class, 'email'])->name('regist.email');
});

Route::middleware('guest')->group(function () {

});

Route::get('/regist_entry', [RegistController::class, 'entry'])->name('regist.entry');

