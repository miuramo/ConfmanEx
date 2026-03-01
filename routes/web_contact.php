<?php

use App\Http\Controllers\ContactController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::resource('contact', ContactController::class);
    Route::post('contact_call_method', [ContactController::class, 'call_method'])->name('contact.call_method');
    Route::post('contact_modify_email', [ContactController::class, 'modify_email'])->name('contact.modify_email');
    Route::get('contact_modify_email', [ContactController::class, 'modify_email'])->name('contact.modify_email');

    Route::post('contact_disable_email', [ContactController::class, 'disable_email'])->name('contact.disable_email');
});

