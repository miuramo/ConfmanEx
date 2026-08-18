<?php

use App\Http\Controllers\CertificateController;
use Illuminate\Support\Facades\Route;


Route::middleware('auth')->group(function () {
    Route::get('/certificate_itmsettings', [CertificateController::class, 'itmsettings'])->name('certificate.itmsettings');
    Route::get('/certificate', [CertificateController::class, 'index'])->name('certificate.index');
    Route::get('/certificate/export/{forPrint?}', [CertificateController::class, 'export'])->name('certificate.export');
});

