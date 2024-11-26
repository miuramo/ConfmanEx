<?php

use App\Http\Controllers\AnnotController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::resource('annot', AnnotController::class);


});

