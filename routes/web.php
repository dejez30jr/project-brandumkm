<?php

use App\Http\Controllers\UmkmExportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});
// Export routes - gunakan middleware web dan auth
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/export/umkm/pdf', [UmkmExportController::class, 'exportPdf'])->name('export.umkm.pdf');
    Route::get('/backup/download/{filename}', function (string $filename) {
        $path = storage_path('app/backups/' . $filename);
        if (!file_exists($path)) {
            abort(404);
        }
        return response()->download($path)->deleteFileAfterSend(true);
    })->name('backup.download')->where('filename', '[a-zA-Z0-9_\-\.]+');
});