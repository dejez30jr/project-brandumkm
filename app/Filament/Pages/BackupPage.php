<?php

namespace App\Filament\Pages;

use App\Models\Kota;
use App\Models\Umkm;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;

class BackupPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';
    protected static ?string $navigationLabel = 'Backup Data';
    protected static ?string $title = 'Backup Management';
    protected static ?string $navigationGroup = 'System';

    protected static string $view = 'filament.pages.backup-page';

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

        $this->form->fill(['backup_type' => 'all']);
    }

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
                            ->options(Umkm::orderBy('nama_usaha')->pluck('nama_usaha', 'id'))
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
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $formData = $this->form->getState();
        $backupType = $formData['backup_type'];
        $kotaId = $formData['kota_id'] ?? null;
        $umkmId = $formData['umkm_id'] ?? null;

        // Build query
        $query = Umkm::with(['kota', 'submittedBy', 'umkmDesign', 'approvedBy']);
        if ($backupType === 'city' && $kotaId) {
            $query->where('kota_id', $kotaId);
        } elseif ($backupType === 'umkm' && $umkmId) {
            $query->where('id', $umkmId);
        }
        $records = $query->get();

        if ($records->isEmpty()) {
            Notification::make()->title('Tidak ada data untuk di-backup')->warning()->send();
            return;
        }

        $suffix = match ($backupType) {
            'city' => '_kota_' . ($records->first()->kota?->nama ?? $kotaId),
            'umkm' => '_umkm_' . $umkmId,
            default => '_all',
        };
        $zipFileName = 'backup_umkm' . $suffix . '_' . date('Y-m-d_H-i-s') . '.zip';
        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }
        $zipPath = $backupDir . '/' . $zipFileName;

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
            Notification::make()->title('Gagal membuat file ZIP')->danger()->send();
            return;
        }

        $filesPath = storage_path('app/public');

        foreach ($records as $umkm) {
            $kotaName = $umkm->kota?->nama ?? 'Tanpa_Kota';
            $folder = "{$kotaName}/umkm_{$umkm->id}_{$umkm->nama_usaha}";

            // Foto UMKM
            $this->addFileToZip($zip, $filesPath, $umkm->foto_depan, "{$folder}/foto/");
            $this->addFileToZip($zip, $filesPath, $umkm->foto_kanan, "{$folder}/foto/");
            $this->addFileToZip($zip, $filesPath, $umkm->foto_kiri, "{$folder}/foto/");
            $this->addFileToZip($zip, $filesPath, $umkm->foto_plang_alfamart, "{$folder}/foto/");
            $this->addFileToZip($zip, $filesPath, $umkm->foto_tampak_jauh, "{$folder}/foto/");

            // Video
            $this->addFileToZip($zip, $filesPath, $umkm->video_validasi, "{$folder}/video/");

            // Design
            $this->addFileToZip($zip, $filesPath, $umkm->design_final, "{$folder}/design/");
            $this->addFileToZip($zip, $filesPath, $umkm->design_gerobak_depan, "{$folder}/design/");
            $this->addFileToZip($zip, $filesPath, $umkm->design_gerobak_kiri, "{$folder}/design/");
            $this->addFileToZip($zip, $filesPath, $umkm->design_gerobak_kanan, "{$folder}/design/");

            // Stiker pemasangan
            $this->addFileToZip($zip, $filesPath, $umkm->stiker_tampak_depan, "{$folder}/pemasangan/");
            $this->addFileToZip($zip, $filesPath, $umkm->stiker_tampak_kanan, "{$folder}/pemasangan/");
            $this->addFileToZip($zip, $filesPath, $umkm->stiker_tampak_kiri, "{$folder}/pemasangan/");
            $this->addFileToZip($zip, $filesPath, $umkm->foto_wide, "{$folder}/pemasangan/");
        }

        // Export Excel data
        $excelPath = storage_path('app/backup_data.xlsx');
        Excel::store(new \App\Exports\UmkmExport($records), 'backup_data.xlsx', 'local');
        if (file_exists($excelPath)) {
            $zip->addFile($excelPath, 'data/data_umkm.xlsx');
        }

        // Export JSON (raw database)
        $jsonPath = storage_path('app/backup_data.json');
        file_put_contents($jsonPath, $records->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        $zip->addFile($jsonPath, 'data/data_umkm.json');

        $zip->close();

        // Cleanup temp files
        @unlink($excelPath);
        @unlink($jsonPath);

        Notification::make()->title('Backup Berhasil Dibuat!')->success()->send();

        return redirect()->to(route('backup.download', ['filename' => $zipFileName]));
    }

    private function addFileToZip(ZipArchive $zip, string $basePath, ?string $relativePath, string $zipFolder): void
    {
        if (!$relativePath) return;
        $fullPath = $basePath . '/' . $relativePath;
        if (file_exists($fullPath)) {
            $zip->addFile($fullPath, $zipFolder . basename($relativePath));
        }
    }
}
