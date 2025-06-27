<?php

use App\Http\Controllers\RegistController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::resource('regist', RegistController::class);
    // Route::get('/annot/{annot}/show/{page?}', [AnnotController::class, 'show'])->name('annot.showpage');
    // Route::post('/annot_postsubmit', [AnnotController::class, 'postsubmit'])->name('annot.postsubmit');
    // // Route::get('/annot_jsubmit/{annot}', [AnnotController::class, 'jsubmit'])->name('annot.submit');
    // // Route::get('/jsubmit/{annot?}', [AnnotController::class, 'jsonsubmit'])->name('annot.jsonsubmit');
    // Route::get('/annot/{annot}/comment_json/{page?}', [AnnotController::class, 'comment_json'])->name('annot.comment_json');

    // Route::post('/annot/{annot}/setpublic', [AnnotController::class, 'setpublic'])->name('annot.setpublic');
});

Route::middleware('guest')->group(function () {
    Route::get('/regist_entry', [RegistController::class, 'entry'])->name('regist.entry');
});
