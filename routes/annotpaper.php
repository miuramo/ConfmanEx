<?php

use App\Http\Controllers\AnnotController;
use App\Http\Controllers\FileController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::resource('annot', AnnotController::class);
    Route::get('/annot/{annot}/{page?}', [AnnotController::class, 'show'])->name('annot.show_page');
    Route::post('/annot_postsubmit', [AnnotController::class, 'postsubmit'])->name('annot.postsubmit');
    // Route::get('/annot_jsubmit/{annot}', [AnnotController::class, 'jsubmit'])->name('annot.submit');
    // Route::get('/jsubmit/{annot?}', [AnnotController::class, 'jsonsubmit'])->name('annot.jsonsubmit');
});

Route::get('/annot/{annot}/{page?}', [AnnotController::class, 'show'])->name('annot.show_page');
