<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Response;
use Filament\Notifications\Notification;
use ZipArchive;

class BackupPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationLabel = 'Backup Data';
    protected static ?string $title = 'Backup Management';
    protected static ?string $navigationGroup = 'System';

    protected static string $view = 'filament.pages.backup-page';

    // Batasi akses secara ketat: Hanya ADMIN yang bisa melihat menu ini
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function mount(): void
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }
    }

    // Fungsi Utama: Membuat file ZIP berisi Database SQL & Semua File Upload (Gambar/Video)
    public function downloadBackup()
    {
        $zipFileName = 'backup_hanzi_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            // 1. Ambil File Upload di folder storage/app/public (Gambar & Video)
            $filesPath = storage_path('app/public');
            if (file_exists($filesPath)) {
                $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($filesPath));
                foreach ($files as $file) {
                    if (!$file->isDir()) {
                        $filePath = $file->getRealPath();
                        $relativePath = 'uploads/' . substr($filePath, strlen($filesPath) + 1);
                        $zip->addFile($filePath, $relativePath);
                    }
                }
            }

            // 2. Dump Database MySQL langsung ke dalam ZIP (Sesuai Konfigurasi .env Anda)
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');
            
            $sqlDumpPath = storage_path('app/database_backup.sql');
            
            // Perintah standar mysqldump
            $command = "mysqldump --user={$dbUser} --password={$dbPass} --host={$dbHost} {$dbName} > {$sqlDumpPath}";
            exec($command);

            if (file_exists($sqlDumpPath)) {
                $zip->addFile($sqlDumpPath, 'database/database_backup.sql');
            }

            $zip->close();

            // Hapus file sementara sql dump setelah dimasukkan ke zip
            if (file_exists($sqlDumpPath)) {
                unlink($sqlDumpPath);
            }

            Notification::make()
                ->title('Backup Berhasil Dibuat!')
                ->success()
                ->send();

            // Lakukan download otomatis ke browser dan hapus file zip di server agar tidak penuh
            return Response::download($zipPath)->deleteFileAfterSend(true);
        }

        Notification::make()
            ->title('Gagal membuat backup')
            ->danger()
            ->send();
    }
}