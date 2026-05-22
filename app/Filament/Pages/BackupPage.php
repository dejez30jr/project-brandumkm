<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Support\Facades\Response;
use Filament\Notifications\Notification;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use App\Models\Kota;
use ZipArchive;

class BackupPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationLabel = 'Backup Data';
    protected static ?string $title = 'Backup Management';
    protected static ?string $navigationGroup = 'System';

    protected static string $view = 'filament.pages.backup-page';

    // State form
    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->role === 'admin';
    }

    public function mount(): void
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        // Initialize form default state
        $this->form->fill([
            'backup_type' => 'all',
        ]);
    }

    // Mendefinisikan Form Filter di Halaman Backup
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Filter Ruang Lingkup Backup')
                    ->description('Tentukan data mana saja yang ingin Anda amankan ke dalam file ZIP.')
                    ->schema([
                        Radio::make('backup_type')
                            ->label('Tipe Backup')
                            ->options([
                                'all' => 'Backup Semua Data',
                                'city' => 'Backup Parsial (Per Kota)',
                            ])
                            ->live() // Memicu perubahan form secara realtime
                            ->required(),

                        Select::make('kota_id')
                            ->label('Pilih Kota')
                            ->placeholder('Pilih kota target backup')
                            ->options(Kota::orderBy('nama')->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            // Hanya muncul jika admin memilih opsi 'city'
                            ->visible(fn ($get) => $get('backup_type') === 'city')
                            // Wajib diisi jika tipe backup adalah per kota
                            ->required(fn ($get) => $get('backup_type') === 'city'),
                    ])
            ])
            ->statePath('data');
    }

    // Fungsi Utama yang dimodifikasi
    public function downloadBackup()
    {
        // Validasi form terlebih dahulu
        $formData = $this->form->getState();
        $backupType = $formData['backup_type'];
        $kotaId = $formData['kota_id'] ?? null;

        $suffix = $backupType === 'city' && $kotaId ? '_kota_' . $kotaId : '_all';
        $zipFileName = 'backup_hanzi' . $suffix . '_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            
            // 1. Ambil File Upload di folder storage/app/public
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

            // 2. Dump Database MySQL (Sesuai Konfigurasi .env Anda)
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');
            
            $sqlDumpPath = storage_path('app/database_backup.sql');
            
            // LOGIKA KONDISIONAL MYSQLDUMP
            if ($backupType === 'city' && $kotaId) {
                // Validasi kotaId harus integer untuk mencegah injection
                $kotaIdSafe = (int) $kotaId;
                $command = sprintf(
                    'mysqldump %s %s %s %s --where=%s %s > %s',
                    escapeshellarg('--user=' . $dbUser),
                    escapeshellarg('--password=' . $dbPass),
                    escapeshellarg('--host=' . $dbHost),
                    escapeshellarg($dbName),
                    escapeshellarg("kota_id={$kotaIdSafe}"),
                    escapeshellarg('umkms'),
                    escapeshellarg($sqlDumpPath)
                );
            } else {
                // Perintah standar full backup
                $command = sprintf(
                    'mysqldump %s %s %s %s > %s',
                    escapeshellarg('--user=' . $dbUser),
                    escapeshellarg('--password=' . $dbPass),
                    escapeshellarg('--host=' . $dbHost),
                    escapeshellarg($dbName),
                    escapeshellarg($sqlDumpPath)
                );
            }
            
            exec($command);

            if (file_exists($sqlDumpPath)) {
                $zip->addFile($sqlDumpPath, 'database/database_backup.sql');
            }

            $zip->close();

            // Hapus file sementara setelah dimasukkan ke zip
            if (file_exists($sqlDumpPath)) {
                unlink($sqlDumpPath);
            }

            Notification::make()
                ->title('Backup ' . ($backupType === 'city' ? 'Per Kota' : 'Semua') . ' Berhasil Dibuat!')
                ->success()
                ->send();

            return Response::download($zipPath)->deleteFileAfterSend(true);
        }

        Notification::make()
            ->title('Gagal membuat backup')
            ->danger()
            ->send();
    }
}