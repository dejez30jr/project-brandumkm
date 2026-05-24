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
                                'all'  => 'Backup Semua Data',
                                'city' => 'Backup Per Kota',
                                'umkm' => 'Backup Per Nama UMKM',
                            ])
                            ->live()
                            ->required(),

                        Select::make('kota_id')
                            ->label('Pilih Kota')
                            ->placeholder('Pilih kota target backup')
                            ->options(Kota::orderBy('nama')->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('backup_type') === 'city')
                            ->required(fn ($get) => $get('backup_type') === 'city'),

                        Select::make('umkm_id')
                            ->label('Pilih UMKM')
                            ->placeholder('Cari nama UMKM...')
                            ->options(\App\Models\Umkm::orderBy('nama_usaha')->pluck('nama_usaha', 'id'))
                            ->searchable()
                            ->preload()
                            ->visible(fn ($get) => $get('backup_type') === 'umkm')
                            ->required(fn ($get) => $get('backup_type') === 'umkm'),
                    ])
            ])
            ->statePath('data');
    }

    public function downloadBackup()
    {
        $formData = $this->form->getState();
        $backupType = $formData['backup_type'];
        $kotaId = $formData['kota_id'] ?? null;
        $umkmId = $formData['umkm_id'] ?? null;

        $suffix = match ($backupType) {
            'city' => '_kota_' . $kotaId,
            'umkm' => '_umkm_' . $umkmId,
            default => '_all',
        };
        $zipFileName = 'backup_umkm' . $suffix . '_' . date('Y-m-d_H-i-s') . '.zip';
        $zipPath = storage_path('app/' . $zipFileName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

            // 1. File uploads
            $filesPath = storage_path('app/public');
            if ($backupType === 'umkm' && $umkmId) {
                // Hanya file milik UMKM ini (folder umkm/{kota_id}/*)
                $umkm = \App\Models\Umkm::find($umkmId);
                if ($umkm) {
                    $umkmFolder = $filesPath . '/umkm/' . $umkm->kota_id;
                    if (file_exists($umkmFolder)) {
                        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($umkmFolder));
                        foreach ($files as $file) {
                            if (!$file->isDir()) {
                                $filePath = $file->getRealPath();
                                $relativePath = 'uploads/' . substr($filePath, strlen($filesPath) + 1);
                                $zip->addFile($filePath, $relativePath);
                            }
                        }
                    }
                }
            } elseif ($backupType === 'city' && $kotaId) {
                $cityFolder = $filesPath . '/umkm/' . $kotaId;
                if (file_exists($cityFolder)) {
                    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($cityFolder));
                    foreach ($files as $file) {
                        if (!$file->isDir()) {
                            $filePath = $file->getRealPath();
                            $relativePath = 'uploads/' . substr($filePath, strlen($filesPath) + 1);
                            $zip->addFile($filePath, $relativePath);
                        }
                    }
                }
            } else {
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
            }

            // 2. Database dump
            $dbName = config('database.connections.mysql.database');
            $dbUser = config('database.connections.mysql.username');
            $dbPass = config('database.connections.mysql.password');
            $dbHost = config('database.connections.mysql.host');
            $sqlDumpPath = storage_path('app/database_backup.sql');

            if ($backupType === 'umkm' && $umkmId) {
                $umkmIdSafe = (int) $umkmId;
                $command = sprintf(
                    'mysqldump %s %s %s %s --where=%s %s > %s',
                    escapeshellarg('--user=' . $dbUser),
                    escapeshellarg('--password=' . $dbPass),
                    escapeshellarg('--host=' . $dbHost),
                    escapeshellarg($dbName),
                    escapeshellarg("id={$umkmIdSafe}"),
                    escapeshellarg('umkms'),
                    escapeshellarg($sqlDumpPath)
                );
            } elseif ($backupType === 'city' && $kotaId) {
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
                $command = sprintf(
                    'mysqldump %s %s %s %s > %s',
                    escapeshellarg('--user=' . $dbUser),
                    escapeshellarg('--password=' . $dbPass),
                    escapeshellarg('--host=' . $dbHost),
                    escapeshellarg($dbName),
                    escapeshellarg($sqlDumpPath)
                );
            }

            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || !file_exists($sqlDumpPath) || filesize($sqlDumpPath) === 0) {
                $zip->close();
                Notification::make()->title('Gagal dump database')->danger()->send();
                return;
            }

            if (file_exists($sqlDumpPath)) {
                $zip->addFile($sqlDumpPath, 'database/database_backup.sql');
            }

            $zip->close();

            if (file_exists($sqlDumpPath)) {
                unlink($sqlDumpPath);
            }

            Notification::make()
                ->title('Backup Berhasil Dibuat!')
                ->success()
                ->send();

            return Response::download($zipPath)->deleteFileAfterSend(true);
        }

        Notification::make()->title('Gagal membuat backup')->danger()->send();
    }
}