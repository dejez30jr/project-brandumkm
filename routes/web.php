<?php

use App\Http\Controllers\UmkmExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Export routes - gunakan middleware web dan auth
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/export/umkm/pdf', [UmkmExportController::class, 'exportPdf'])->name('export.umkm.pdf');
});