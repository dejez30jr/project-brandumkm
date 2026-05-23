<?php

namespace App\Filament\Resources;

use App\Exports\UmkmExport;
use App\Filament\Resources\UmkmResource\Pages;
use App\Models\Kota;
use App\Models\Umkm;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Facades\Excel;

class UmkmResource extends Resource
{
    protected static ?string $model = Umkm::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationGroup = 'Data UMKM';
    protected static ?string $label = 'UMKM';
    protected static ?string $pluralLabel = 'Data UMKM';

    // akses role design, client, admin
    public static function canAccess(): bool
    {
        return in_array(auth()->user()?->role, ['design', 'pic_lapangan', 'client', 'admin']);
    }

    // select data pic
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
{
    $query = parent::getEloquentQuery();

    $user = auth()->user();

    // Jika role pic_lapangan, tampilkan hanya data miliknya
    if ($user && $user->role === 'pic_lapangan') {
        $query->where('submitted_by', $user->id);
    }

    return $query;
}

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['client', 'admin'])) return null;

        $count = Umkm::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $user = auth()->user();
        if (!$user || !in_array($user->role, ['client', 'admin'])) return null;

        $count = Umkm::where('status', 'pending')->count();
        return $count > 5 ? 'danger' : 'warning';
    }
    

//  tombol create disni yang bisa akses hanya oleh pic_lapangan
  public static function canCreate(): bool
{
    $user = auth()->user();
    return $user && in_array($user->role, ['pic_lapangan']);
}
public static function canEdit($record): bool
{
    $user = auth()->user();
    if (!$user || $user->role !== 'pic_lapangan') return false;
    return $record->status === 'pending';
}

// tombol delete  hanya untuk pic_lapangan dan admin
public static function canDelete($record): bool
{
    $user = auth()->user();
    return in_array($user->role, ['admin', 'pic_lapangan']);
}

    // Helper function untuk menghitung m2 dari W x H (cm)
    private static function calculateM2(?float $width, ?float $height): float
    {
        if (!$width || !$height) return 0;
        return round(($width * $height) / 10000, 2); // cm² to m²
    }

    // Helper function untuk menghitung total panel
    private static function calculatePanelTotal(Get $get, string $prefix): float
    {
        $atas = self::calculateM2(
            floatval($get("{$prefix}_atas_w")),
            floatval($get("{$prefix}_atas_h"))
        );
        $tengah = self::calculateM2(
            floatval($get("{$prefix}_tengah_w")),
            floatval($get("{$prefix}_tengah_h"))
        );
        $bawah = self::calculateM2(
            floatval($get("{$prefix}_bawah_w")),
            floatval($get("{$prefix}_bawah_h"))
        );
        
        return round($atas + $tengah + $bawah, 2);
    }

   public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\Wizard::make([

                // STEP 1
                Forms\Components\Wizard\Step::make('Data Pemilik')
                    ->icon('heroicon-o-user')
                   ->schema([
            Forms\Components\Hidden::make('submitted_by')
                ->default(auth()->id()),

            Forms\Components\TextInput::make('nama_pemilik')
                ->label('Nama Pemilik')
                ->placeholder('Nama Sesuai KTP')
                ->required(),

                        Forms\Components\TextInput::make('nama_usaha')
                            ->label('Nama Usaha')
                            ->required(),

                        Forms\Components\Textarea::make('alamat_usaha')
                            ->label('Alamat Usaha')
                            ->required()
                            ->rows(3),

                        Forms\Components\TextInput::make('no_wa')
                            ->label('No. WhatsApp')
                            ->tel()
                             ->unique(table: Umkm::class, column: 'no_wa', ignoreRecord: true)
                            ->required(),

                        Forms\Components\TextInput::make('radius')
                            ->label('Radius dari Alfamart')
                            ->placeholder('Contoh:50,20...'),

                        Forms\Components\TextInput::make('request_text')
                            ->label('Teks Branding yang Diminta')
                            ->placeholder('Contoh: Aneka Gorengan UMY')
                            ->columnSpanFull(),

                        Forms\Components\TimePicker::make('jam_buka')
                            ->label('Jam Buka')
                            ->seconds(false),

                        Forms\Components\TimePicker::make('jam_tutup')
                            ->label('Jam Tutup')
                            ->seconds(false),

                        Forms\Components\Select::make('kota_id')
    ->label('Kota')
    ->options(Kota::pluck('nama', 'id'))
    ->searchable()
    ->preload()

    ->createOptionAction(function ($action) {
        return $action->label('Tambah Kota');
    })

    ->createOptionForm([
        Forms\Components\TextInput::make('nama')
            ->label('Nama Kota')
            ->required(),
    ])

    ->createOptionUsing(function (array $data) {
        return Kota::create($data)->id;
    }),
                    ])
                    ->columns(2),

                // STEP 2
                Forms\Components\Wizard\Step::make('Data Rekening')
                    ->icon('heroicon-o-credit-card')
                    ->schema([
                        Forms\Components\TextInput::make('no_rekening')
                            ->label('No. Rekening')
                            ->unique(table: Umkm::class, column: 'no_rekening', ignoreRecord: true)
                            ->required(),

                        Forms\Components\Select::make('nama_bank') // Sesuaikan dengan nama kolom di database Anda
    ->label('Pilih Bank')
    ->placeholder('Pilih bank penyedia')
    ->options([
        'BCA' => 'BCA',
        'Mandiri' => 'Mandiri',
        'BNI' => 'BNI',
        'BRI' => 'BRI',
        'Danamon' => 'Danamon',
        'Permata' => 'Permata',
        'CIMB Niaga' => 'CIMB Niaga',
        'Maybank' => 'Maybank',
        'Panin Bank' => 'Panin Bank',
        'Bank Syariah Indonesia (BSI)' => 'Bank Syariah Indonesia (BSI)',
        'Bank Jago' => 'Bank Jago',
        'bank jatim' => 'bank jatim',
        'bank jateng' => 'bank jateng',
        'Bank Mega' => 'Bank Mega',
        'Bank Bukopin' => 'Bank Bukopin',
        'Bank OCBC NISP' => 'Bank OCBC NISP',
        'Bank BTN' => 'Bank BTN',
        'Bank BTPN' => 'Bank BTPN',
        'Bank DKI' => 'Bank DKI',
        'Bank Sinarmas' => 'Bank Sinarmas',
        'Bank Jabar' => 'Bank Jabar',
        'Bank BRI Syariah' => 'Bank BRI Syariah',
        'Bank Danamon Syariah' => 'Bank Danamon Syariah',
        'Bank Permata Syariah' => 'Bank Permata Syariah',
        'Bank Panin Syariah' => 'Bank Panin Syariah',
        'Bank OCBC NISP Syariah' => 'Bank OCBC NISP Syariah',
        'Bank Blue Bca' => 'Bank Blue Bca',
        'Bank Bukopin Syariah' => 'Bank Bukopin Syariah',
    ])
    ->searchable() // Biar tim design/user bisa mengetik nama bank (tidak capek scroll)
    ->preload()    // Memuat semua data di awal agar pencarian terasa instan dan cepat
    ->required(),  // Opsional, jika wajib diisi

                        Forms\Components\TextInput::make('atas_nama_rekening')
                            ->label('Atas Nama')
                            ->required()
                            ->placeholder('Sesuaikan dengan nama di rekening bank'),
                    ])
                    ->columns(2),

                // STEP 3
                Forms\Components\Wizard\Step::make('Lokasi')
                    ->icon('heroicon-o-map-pin')
                    ->schema([
                         // GPS WAJIB — tidak bisa lanjut tanpa koordinat
        Forms\Components\Placeholder::make('auto_location_trigger')
            ->label('')
            ->content(new \Illuminate\Support\HtmlString(
                '<div 
                    x-data="{
                        loading: false,
                        success: false,
                        blocked: true,
                        denied: false,
                        errorMsg: \'\',
                        attempts: 0,
                        async init() {
                            // Cek status permission dulu
                            if (navigator.permissions) {
                                try {
                                    let result = await navigator.permissions.query({name: \"geolocation\"});
                                    if (result.state === \"denied\") {
                                        this.denied = true;
                                        this.errorMsg = \"Lokasi diblokir permanen.\";
                                        return;
                                    }
                                    // Listen perubahan permission (user ubah di settings)
                                    result.onchange = () => {
                                        if (result.state === \"granted\") {
                                            this.denied = false;
                                            this.getLocation();
                                        } else if (result.state === \"denied\") {
                                            this.denied = true;
                                            this.errorMsg = \"Lokasi diblokir permanen.\";
                                        }
                                    };
                                } catch(e) {}
                            }
                            this.getLocation();
                        },
                        getLocation() {
                            if (this.denied) return;
                            this.loading = true;
                            this.errorMsg = \'\';
                            this.attempts++;

                            if (!navigator.geolocation) {
                                this.loading = false;
                                this.errorMsg = \'Perangkat tidak mendukung GPS.\';
                                return;
                            }

                            navigator.geolocation.getCurrentPosition(
                                (position) => {
                                    let lat = position.coords.latitude.toFixed(8);
                                    let lng = position.coords.longitude.toFixed(8);
                                    $wire.set(\'data.latitude\', lat);
                                    $wire.set(\'data.longitude\', lng);
                                    $wire.set(\'data.sharelock_url\', \'https://www.google.com/maps?q=\' + lat + \',\' + lng);
                                    this.loading = false;
                                    this.success = true;
                                    this.blocked = false;
                                    this.denied = false;
                                },
                                (err) => {
                                    this.loading = false;
                                    if (err.code === 1) {
                                        this.denied = true;
                                        this.errorMsg = \'Lokasi diblokir permanen.\';
                                    } else if (err.code === 2) {
                                        this.errorMsg = \'GPS tidak aktif.\';
                                    } else {
                                        if (this.attempts < 5) {
                                            setTimeout(() => this.getLocation(), 2000);
                                            return;
                                        }
                                        this.errorMsg = \'Gagal mengambil lokasi.\';
                                    }
                                },
                                { enableHighAccuracy: true, timeout: 30000, maximumAge: 0 }
                            );
                        }
                    }"
                >
                    <!-- OVERLAY BLOCKER — menutupi seluruh halaman sampai GPS berhasil -->
                    <div x-show="blocked" x-transition class="fixed inset-0 z-[9999] bg-black/80 flex items-center justify-center p-4" style="backdrop-filter: blur(6px);">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-2xl max-w-md w-full p-6 text-center">
                            <!-- Icon GPS -->
                            <div class="mx-auto w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>

                            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">GPS WAJIB AKTIF</h2>
                            <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Anda <strong>tidak dapat melanjutkan</strong> tanpa mengaktifkan lokasi.<br>Ini adalah <strong>kebijakan perusahaan</strong>.</p>

                            <!-- Loading state -->
                            <div x-show="loading" class="flex items-center justify-center gap-2 text-blue-600 mb-4">
                                <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span class="font-medium">Mengambil lokasi...</span>
                            </div>

                            <!-- DENIED: instruksi reset permission -->
                            <div x-show="denied && !loading" class="mb-4">
                                <div class="bg-red-50 dark:bg-red-900/20 border-2 border-red-300 dark:border-red-700 rounded-lg p-4 mb-3 text-left">
                                    <p class="text-red-700 dark:text-red-400 font-bold text-sm mb-2">⛔ LOKASI DIBLOKIR</p>
                                    <p class="text-red-600 dark:text-red-300 text-xs mb-3">Anda telah menolak izin lokasi. Untuk melanjutkan, Anda HARUS mengubah pengaturan:</p>
                                    <div class="text-xs text-gray-700 dark:text-gray-300 space-y-1.5 bg-white dark:bg-gray-900 rounded p-3 border">
                                        <p class="font-bold text-gray-900 dark:text-white">📱 Di HP (Chrome/WebView):</p>
                                        <p>1. Tap ikon 🔒 di address bar (atau ⋮ menu)</p>
                                        <p>2. Pilih <strong>Izin Situs / Site Settings</strong></p>
                                        <p>3. Tap <strong>Lokasi</strong> → ubah ke <strong>Izinkan</strong></p>
                                        <p>4. Kembali ke halaman ini & tekan tombol di bawah</p>
                                        <hr class="my-2 border-gray-200">
                                        <p class="font-bold text-gray-900 dark:text-white">📱 Di Aplikasi (WebView APK):</p>
                                        <p>1. Buka <strong>Pengaturan HP</strong></p>
                                        <p>2. Pilih <strong>Aplikasi</strong> → cari aplikasi ini</p>
                                        <p>3. Tap <strong>Izin</strong> → <strong>Lokasi</strong> → <strong>Izinkan</strong></p>
                                        <p>4. Kembali ke aplikasi & tekan tombol di bawah</p>
                                    </div>
                                </div>
                            </div>

                            <!-- GPS OFF: instruksi nyalakan -->
                            <div x-show="errorMsg && !denied && !loading" class="mb-4">
                                <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-300 rounded-lg p-3 mb-3 text-left">
                                    <p class="text-yellow-800 dark:text-yellow-300 font-semibold text-sm" x-text="errorMsg"></p>
                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-2 space-y-1">
                                        <p>1. Geser notification bar ke bawah</p>
                                        <p>2. Tap ikon <strong>Lokasi/GPS</strong> hingga menyala</p>
                                        <p>3. Tekan tombol di bawah</p>
                                    </div>
                                </div>
                            </div>

                            <button x-show="!loading" type="button" @click="denied = false; attempts = 0; getLocation()" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 active:bg-blue-800 transition text-sm uppercase tracking-wide">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
                                SUDAH AKTIF — COBA LAGI
                            </button>
                        </div>
                    </div>

                    <!-- Success indicator (setelah GPS berhasil) -->
                    <div x-show="success" x-transition class="flex items-center gap-2 text-success-600 p-4 bg-success-50 dark:bg-success-900/20 rounded-lg mb-4 border border-success-200">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="font-medium">✅ Lokasi GPS berhasil diambil!</span>
                    </div>
                </div>'
            ))
            ->columnSpanFull(),

        Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitude')
                    ->required()
                    ->numeric()
                    ->step(0.00000001)
                    ->readOnly()
                    ->placeholder('Wajib dari GPS — nyalakan lokasi')
                    ->helperText('Koordinat otomatis dari GPS. Tidak bisa diisi manual.')
                    ->live(),
                    
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitude')
                    ->required()
                    ->numeric()
                    ->step(0.00000001)
                    ->readOnly()
                    ->placeholder('Wajib dari GPS — nyalakan lokasi')
                    ->helperText('Koordinat otomatis dari GPS. Tidak bisa diisi manual.')
                    ->live(),
            ]),

        Forms\Components\TextInput::make('sharelock_url')
            ->label('Google Maps URL')
            ->required()
            ->url()
            ->readOnly()
            ->placeholder('Otomatis terisi dari GPS...')
            ->columnSpanFull()
            ->suffixAction(
                Forms\Components\Actions\Action::make('openMap')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Get $get) => $get('sharelock_url'))
                    ->openUrlInNewTab()
                    ->visible(fn (Get $get) => !empty($get('sharelock_url')))
            ),

        // Preview Map
        Forms\Components\Placeholder::make('map_preview')
            ->label('Preview Lokasi')
            ->content(function (Get $get): \Illuminate\Support\HtmlString {
                $lat = $get('latitude');
                $lng = $get('longitude');
                
                if ($lat && $lng) {
                    return new \Illuminate\Support\HtmlString(
                        "<iframe 
                            width='100%' 
                            height='250' 
                            style='border:0; border-radius: 8px;' 
                            loading='lazy' 
                            allowfullscreen 
                            src='https://maps.google.com/maps?q={$lat},{$lng}&z=17&output=embed'>
                        </iframe>"
                    );
                }
                
                return new \Illuminate\Support\HtmlString(
                    '<div class="bg-gray-100 dark:bg-gray-800 rounded-lg p-6 text-center text-gray-500">
                        <svg class="mx-auto h-10 w-10 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <p class="text-sm">Menunggu lokasi GPS...</p>
                    </div>'
                );
            })
            ->columnSpanFull(),
                    ]),

                // STEP 4
                Forms\Components\Wizard\Step::make('Ukuran Panel')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                     // Info minimum area
        Forms\Components\Placeholder::make('info_minimum')
            ->label('')
            // tambahin keterangan jika data ukuran tidak ada cukup isi 0
         ->content(new \Illuminate\Support\HtmlString('
    <div style="display: flex; flex-direction: column; gap: 4px;">
        <span style="color: #facc15 !important; font-weight: 600; font-size: 0.85rem;">
            ⚠️ Jika tidak ada ukuran panel, isi dengan 0 (nol)
        </span>
        <span style="color: #facc15 !important; font-weight: 600; font-size: 0.85rem;">
            📐 Minimum total area branding: 1.5 M²
        </span>
    </div>
')),

        // ========== PANEL DEPAN ==========
        Forms\Components\Section::make('Panel Depan')
            ->description(function (Get $get): string {
                $atas = self::calculateM2(floatval($get('depan_atas_w')), floatval($get('depan_atas_h')));
                $bawah = self::calculateM2(floatval($get('depan_bawah_w')), floatval($get('depan_bawah_h')));
                return round($atas + $bawah, 2) . ' M²';
            })
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('depan_atas_label')->label('')->content('Bagian Atas'),
                        Forms\Components\TextInput::make('depan_atas_w')->required()->label('P (cm)')->placeholder('contoh: 163')->numeric()->live(onBlur: true),
                        Forms\Components\TextInput::make('depan_atas_h')->label('T (cm)')->required()->placeholder('contoh: 20')->numeric()->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('depan_panel_atas_m2'),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('depan_bawah_label')->label('')->content('Bagian Bawah'),
                        Forms\Components\TextInput::make('depan_bawah_w')->label('P (cm)')->required()->placeholder('contoh: 163')->numeric()->live(onBlur: true),
                        Forms\Components\TextInput::make('depan_bawah_h')->label('T (cm)')->required()->numeric()->placeholder('contoh: 62')->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('depan_panel_bawah_m2'),
            ])
            ->collapsible()
            ->extraAttributes(['class' => 'border-l-4 border-l-primary-500']),

        // ========== PANEL KANAN ==========
        Forms\Components\Section::make('Panel Kanan')
            ->description(function (Get $get): string {
                $atas = self::calculateM2(floatval($get('kanan_atas_w')), floatval($get('kanan_atas_h')));
                $bawah = self::calculateM2(floatval($get('kanan_bawah_w')), floatval($get('kanan_bawah_h')));
                return round($atas + $bawah, 2) . ' M²';
            })
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('kanan_atas_label')->label('')->content('Bagian Atas'),
                        Forms\Components\TextInput::make('kanan_atas_w')->label('P (cm)')->required()->placeholder('contoh: 54')->numeric()->live(onBlur: true),
                        Forms\Components\TextInput::make('kanan_atas_h')->label('T (cm)')->numeric()->placeholder('contoh: 20')->required()->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('kanan_panel_atas_m2'),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('kanan_bawah_label')->label('')->content('Bagian Bawah'),
                        Forms\Components\TextInput::make('kanan_bawah_w')->label('P (cm)')->numeric()->placeholder('contoh: 49')->required()->live(onBlur: true),
                        Forms\Components\TextInput::make('kanan_bawah_h')->label('T (cm)')->numeric()->placeholder('contoh: 58')->required()->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('kanan_panel_bawah_m2'),
            ])
            ->collapsible()
            ->extraAttributes(['class' => 'border-l-4 border-l-success-500']),

        // ========== PANEL KIRI ==========
        Forms\Components\Section::make('Panel Kiri')
            ->description(function (Get $get): string {
                $atas = self::calculateM2(floatval($get('kiri_atas_w')), floatval($get('kiri_atas_h')));
                $bawah = self::calculateM2(floatval($get('kiri_bawah_w')), floatval($get('kiri_bawah_h')));
                return round($atas + $bawah, 2) . ' M²';
            })
            ->schema([
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('kiri_atas_label')->label('')->content('Bagian Atas'),
                        Forms\Components\TextInput::make('kiri_atas_w')->label('P (cm)')->placeholder('contoh: 54')->numeric()->required()->live(onBlur: true),
                        Forms\Components\TextInput::make('kiri_atas_h')->label('T (cm)')->placeholder('contoh: 20')->numeric()->required()->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('kiri_panel_atas_m2'),
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('kiri_bawah_label')->label('')->content('Bagian Bawah'),
                        Forms\Components\TextInput::make('kiri_bawah_w')->label('P (cm)')->required()->placeholder('contoh: 54')->numeric()->live(onBlur: true),
                        Forms\Components\TextInput::make('kiri_bawah_h')->label('T (cm)')->numeric()->placeholder('contoh: 62')->required()->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('kiri_panel_bawah_m2'),
            ])
            ->collapsible()
            ->extraAttributes(['class' => 'border-l-4 border-l-warning-500']),
              // Total Area Branding
        Forms\Components\Placeholder::make('total_area_display')
            ->label('Total Area Branding')
            ->content(function (Get $get): \Illuminate\Support\HtmlString {
                $depan = self::calculateM2(floatval($get('depan_atas_w')), floatval($get('depan_atas_h')))
                       + self::calculateM2(floatval($get('depan_bawah_w')), floatval($get('depan_bawah_h')));
                $kanan = self::calculateM2(floatval($get('kanan_atas_w')), floatval($get('kanan_atas_h')))
                       + self::calculateM2(floatval($get('kanan_bawah_w')), floatval($get('kanan_bawah_h')));
                $kiri  = self::calculateM2(floatval($get('kiri_atas_w')), floatval($get('kiri_atas_h')))
                       + self::calculateM2(floatval($get('kiri_bawah_w')), floatval($get('kiri_bawah_h')));
                $total = round($depan + $kanan + $kiri, 2);
                
                if ($total >= 1.5) {
                    return new \Illuminate\Support\HtmlString(
                        "<span class='text-success-600 text-xl font-bold'>{$total} M² (Memenuhi Kriteria)</span>"
                    );
                } else {
                    return new \Illuminate\Support\HtmlString(
                        "<span class='text-danger-600 text-xl font-bold'>{$total} M² (Belum Memenuhi Kriteria)</span>"
                    );
                }
            })
            ->columnSpanFull(),
                    ]),

                // STEP 5
                Forms\Components\Wizard\Step::make('Foto')
                    ->icon('heroicon-o-photo')
                    ->schema([
                         Forms\Components\Grid::make(2)
                ->schema([
                   Forms\Components\Section::make('Foto')
    ->schema([

        Forms\Components\Grid::make(2)
            ->schema([
Forms\Components\FileUpload::make('foto_depan')
    ->required()
    ->label('FOTO DEPAN')
    ->disk('public')
    ->directory(fn (Forms\Get $get) => 'umkm/' . ($get('kota_id') ?: 'temp') . '/foto')
    ->image()
    ->visibility('public')
    ->imagePreviewHeight('200')
    ->loadingIndicatorPosition('left')
    ->panelAspectRatio('2:1')
    ->panelLayout('integrated')
    ->removeUploadedFileButtonPosition('right')
    ->uploadProgressIndicatorPosition('left')
    ->openable()
    ->downloadable()
    ->previewable(),

Forms\Components\FileUpload::make('foto_kanan')
    ->required()
    ->label('FOTO KANAN')
    ->disk('public')
    ->directory(fn (Forms\Get $get) => 'umkm/' . ($get('kota_id') ?: 'temp') . '/foto')
    ->image()
    ->visibility('public')
    ->imagePreviewHeight('200')
    ->loadingIndicatorPosition('left')
    ->panelAspectRatio('2:1')
    ->panelLayout('integrated')
    ->removeUploadedFileButtonPosition('right')
    ->uploadProgressIndicatorPosition('left')
    ->openable()
    ->downloadable()
    ->previewable(),

Forms\Components\FileUpload::make('foto_kiri')
    ->required()
    ->label('FOTO KIRI')
    ->disk('public')
    ->directory(fn (Forms\Get $get) => 'umkm/' . ($get('kota_id') ?: 'temp') . '/foto')
    ->image()
    ->visibility('public')
    ->imagePreviewHeight('200')
    ->loadingIndicatorPosition('left')
    ->panelAspectRatio('2:1')
    ->panelLayout('integrated')
    ->removeUploadedFileButtonPosition('right')
    ->uploadProgressIndicatorPosition('left')
    ->openable()
    ->downloadable()
    ->previewable(),

Forms\Components\FileUpload::make('foto_plang_alfamart')
    ->required()
    ->label('FOTO WIDE JARAK JAUH (PLANG ALFAMART)')
    ->disk('public')
    ->directory(fn (Forms\Get $get) => 'umkm/' . ($get('kota_id') ?: 'temp') . '/foto')
    ->image()
    ->visibility('public')
    ->imagePreviewHeight('200')
    ->loadingIndicatorPosition('left')
    ->panelAspectRatio('2:1')
    ->panelLayout('integrated')
    ->removeUploadedFileButtonPosition('right')
    ->uploadProgressIndicatorPosition('left')
    ->openable()
    ->downloadable()
    ->previewable(),

Forms\Components\FileUpload::make('foto_tampak_jauh')
    ->required()
    ->label('FOTO TAMPAK JAUH (KESELURUHAN AREA)')
    ->disk('public')
    ->directory(fn (Forms\Get $get) => 'umkm/' . ($get('kota_id') ?: 'temp') . '/foto')
    ->image()
    ->visibility('public')
    ->imagePreviewHeight('200')
    ->loadingIndicatorPosition('left')
    ->panelAspectRatio('2:1')
    ->panelLayout('integrated')
    ->removeUploadedFileButtonPosition('right')
    ->uploadProgressIndicatorPosition('left')
    ->openable()
    ->downloadable()
    ->previewable(),

            ])

    ])
                ]),

        // Section Video Validasi
        Forms\Components\Section::make('VIDEO VALIDASI JIKA ALFAMART TIDAK TERLIHAT ATAU TERHALANAG (OPSIONAL)')
            ->schema([
                Forms\Components\FileUpload::make('video_validasi')
                  ->label('UPLOAD VIDEO (MP4) max 2 menit / 50MB — Wajib jika Alfamart tidak terlihat di foto')
                     ->maxSize(51200) // 50MB dalam KB (video 2 menit bisa 20-50MB tergantung kualitas)
                    ->disk('public')
                    ->directory(fn (Forms\Get $get) => 'umkm/' . ($get('kota_id') ?: 'temp') . '/video')
                    ->visibility('public')
                    ->acceptedFileTypes(['video/mp4', 'video/quicktime', 'video/x-msvideo', 'video/3gpp', 'video/3gpp2'])
                    ->helperText('Rekam dari lokasi gerobak sampai terlihat Alfamart. Max 2 menit. Format: MP4, MOV, AVI, 3GP.')
                    ->placeholder('Klik untuk upload video'),
            ])
            ->collapsible(),

        // Section Catatan PIC Lapangan
        Forms\Components\Section::make('Catatan Tambahan')
            ->schema([
                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan PIC Lapangan')
                    ->placeholder('Contoh: Lokasi sebelah Alfamart, area UMY')
                    ->rows(3),
            ])
            ->collapsible(),
                    ]),

            ])
            ->columnSpanFull()
            ->persistStepInQueryString()
            ->skippable(false),

        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_usaha')
                    ->label('Nama Usaha')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_pemilik')
                    ->label('Pemilik')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kota.nama')
                    ->label('Kota')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_area_branding')
                    ->label('Total Area (m2)')
                    ->sortable(),
                Tables\Columns\IconColumn::make('memenuhi_kriteria')
                    ->label('Kriteria')
                    ->boolean(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state === 'pending' => 'warning',
                        in_array($state, ['approved', 'design_approved', 'branded']) => 'success',
                        in_array($state, ['rejected', 'revision_needed']) => 'danger',
                        in_array($state, ['designing', 'design_review']) => 'info',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'designing' => 'Sedang Didesain',
                        'design_review' => 'Review Design',
                        'design_approved' => 'Design OK',
                        'revision_needed' => 'Perlu Revisi',
                        'branded' => 'Terbranding',
                        default => ucfirst($state),
                    }),
                Tables\Columns\TextColumn::make('submittedBy.name')
                    ->label('PIC'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Submit')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
             ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'designing' => 'Sedang Didesain',
                        'design_review' => 'Design Menunggu Review',
                        'design_approved' => 'Design Disetujui',
                        'revision_needed' => 'Perlu Revisi Design',
                        'branded' => 'Sudah Terbranding',
                    ]),
                Tables\Filters\SelectFilter::make('kota_id')
                    ->label('Kota')
                    ->options(Kota::pluck('nama', 'id')),
                Tables\Filters\TernaryFilter::make('memenuhi_kriteria')
                    ->label('Memenuhi Kriteria'),
                Filter::make('created_at')
    ->label('Tanggal Submit')
    ->form([
        DatePicker::make('created_from')->label('Dari Tanggal'),
        DatePicker::make('created_until')->label('Sampai Tanggal'),
    ])
    ->query(function (Builder $query, array $data): Builder {
        return $query
            ->when(
                $data['created_from'],
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
            )
            ->when(
                $data['created_until'],
                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
            );
    })
            ])
            ->actions([
        Tables\Actions\ViewAction::make()
    ->slideOver() // optional biar full kanan
    ->infolist([


    // Tampilkan alasan reject jika statusnya rejected
\Filament\Infolists\Components\TextEntry::make('alasan_reject')
    ->label('Alasan Penolakan (Reject)')
    ->placeholder('Tidak ada alasan tertulis.')
    ->color('danger')
    ->weight('bold')
    ->icon('heroicon-m-exclamation-triangle')
    ->iconColor('danger')
    
    // KUNCI UTAMA: Menggunakan CSS Murni / Inline Styles
    ->extraAttributes([
        'style' => '
            margin-top: 8px;
            padding: 16px;
            background-color: rgba(239, 68, 68, 0.08); /* Warna merah transparan soft */
            border-left: 4px solid #ef4444;            /* Garis vertikal merah tegas */
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            white-space: normal;
            word-break: break-word;
        '
    ])
    ->visible(fn ($record) => $record?->status === 'rejected'),

        // DATA PEMILIK
        \Filament\Infolists\Components\Section::make('Data Pemilik')
            ->schema([
                \Filament\Infolists\Components\TextEntry::make('nama_pemilik')
                    ->label('Nama Pemilik'),

                \Filament\Infolists\Components\TextEntry::make('nama_usaha')
                    ->label('Nama Usaha'),

                \Filament\Infolists\Components\TextEntry::make('alamat_usaha')
                    ->label('Alamat'),

                \Filament\Infolists\Components\TextEntry::make('no_wa')
                    ->label('No WhatsApp'),

                \Filament\Infolists\Components\TextEntry::make('radius')
                    ->label('Radius Alfamart'),

                \Filament\Infolists\Components\TextEntry::make('kota.nama')
                    ->label('Kota'),
            ])
            ->columns(2),

        // REKENING
        \Filament\Infolists\Components\Section::make('Data Rekening')
            ->schema([
                \Filament\Infolists\Components\TextEntry::make('no_rekening')
                    ->label('No Rekening'),

                \Filament\Infolists\Components\TextEntry::make('nama_bank')
                    ->label('Bank'),

                \Filament\Infolists\Components\TextEntry::make('atas_nama_rekening')
                    ->label('Atas Nama'),
            ])
            ->columns(3),

        // LOKASI
        \Filament\Infolists\Components\Section::make('Lokasi')
            ->schema([

                \Filament\Infolists\Components\TextEntry::make('latitude')
                    ->label('Latitude'),

                \Filament\Infolists\Components\TextEntry::make('longitude')
                    ->label('Longitude'),

                \Filament\Infolists\Components\TextEntry::make('sharelock_url')
                    ->label('Google Maps')
                    ->url(fn ($record) => $record->sharelock_url)
                    ->openUrlInNewTab(),
            ])
            ->columns(2),

       // UKURAN PANEL
\Filament\Infolists\Components\Section::make('Ukuran Panel')
    ->schema([
        // Gunakan ViewEntry kustom untuk merender tabel HTML murni
        \Filament\Infolists\Components\ViewEntry::make('ukuran_panel_table')
            ->view('filament.infolists.components.tabel-panel')
            ->columnSpanFull(),
    ]),
// FOTO
\Filament\Infolists\Components\Section::make('Foto')
    ->schema([
// PANGGIL LIGHTBOX MODAL DI SINI (Agar selalu aktif & tidak bergantung pada ada/tidaknya video)
        \Filament\Infolists\Components\ViewEntry::make('image_lightbox')
            ->view('filament.infolists.components.image-lightbox')
            ->columnSpanFull(), // Makan space full tapi tidak terlihat karena modalnya 'display: none'

        \Filament\Infolists\Components\ImageEntry::make('foto_depan')
            ->label('Foto Depan')
            ->height(200)
            ->extraAttributes(fn ($record) => [
                'class' => 'cursor-pointer hover:scale-105 transition duration-300',
                'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->foto_depan) . '" })',
            ]),

        \Filament\Infolists\Components\ImageEntry::make('foto_kanan')
            ->label('Foto Kanan')
            ->height(200)
            ->extraAttributes(fn ($record) => [
                'class' => 'cursor-pointer hover:scale-105 transition duration-300',
                'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->foto_kanan) . '" })',
            ]),

        \Filament\Infolists\Components\ImageEntry::make('foto_kiri')
            ->label('Foto Kiri')
            ->height(200)
            ->extraAttributes(fn ($record) => [
                'class' => 'cursor-pointer hover:scale-105 transition duration-300',
                'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->foto_kiri) . '" })',
            ]),

        \Filament\Infolists\Components\ImageEntry::make('foto_plang_alfamart')
            ->label('Foto jarak dekat plang Alfamart')
            ->height(200)
            ->extraAttributes(fn ($record) => [
                'class' => 'cursor-pointer hover:scale-105 transition duration-300',
                'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->foto_plang_alfamart) . '" })',
            ]),

        // Video Entry menggunakan ViewEntry murni tanpa modal
        \Filament\Infolists\Components\ViewEntry::make('video_validasi')
            ->label('Video Validasi')
            ->view('filament.infolists.components.video-player')
            ->visible(fn ($record) => !empty($record->video_validasi)),
    ])
    ->columns(2),

    // acc design gerobak
      \Filament\Infolists\Components\Section::make('Design Gerobak')
                ->description('Design final dan tampak gerobak yang sudah disetujui.')
                ->icon('heroicon-o-paint-brush')
                ->schema([
                    \Filament\Infolists\Components\ImageEntry::make('design_final')
                    ->label('Design Final')
                    ->height(220)
                    ->columnSpanFull() 
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_final) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_final)),

                    \Filament\Infolists\Components\ImageEntry::make('design_gerobak_depan')
                    ->label('Gerobak Tampak Depan')
                    ->height(200)
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_gerobak_depan) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_gerobak_depan)),

                    \Filament\Infolists\Components\ImageEntry::make('design_gerobak_kiri')
                    ->label('Gerobak Tampak Kiri')
                    ->height(200)
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_gerobak_kiri) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_gerobak_kiri)),

                    \Filament\Infolists\Components\ImageEntry::make('design_gerobak_kanan')
                    ->label('Gerobak Tampak Kanan')
                    ->height(200)
                    ->extraAttributes(fn ($record) => [
                        'class' => 'cursor-pointer hover:scale-105 transition duration-300 rounded-lg overflow-hidden',
                        'x-on:click' => '$dispatch("open-preview-modal", { src: "' . asset('storage/' . $record->design_gerobak_kanan) . '" })',
                    ])
                    ->visible(fn ($record) => !empty($record->design_gerobak_kanan)),
                ])
    ->columns(2)
    ->collapsible() // section bisa diciutkan
    ->visible(fn ($record) =>
        !empty($record->design_final) ||
        !empty($record->design_gerobak_depan) ||
        !empty($record->design_gerobak_kiri) ||
        !empty($record->design_gerobak_kanan)
    ), // section hanya muncul kalau ada minimal 1 gambar

    ])
    ->modalWidth('7xl'),
              Tables\Actions\EditAction::make()
    ->visible(fn () => in_array(auth()->user()?->role, ['pic_lapangan'])),
                
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Umkm $record) => 
                        $record->status === 'pending' && 
                        (auth()->user()->isClient())
                    )
                    ->action(function (Umkm $record) {
                        $record->update([
                            'status' => 'approved',
                            'approved_at' => now(),
                            'approved_by' => auth()->id(),
                        ]);
                    }),
                
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Textarea::make('alasan_reject')
                            ->label('Alasan Reject')
                            ->required(),
                    ])
                    ->visible(fn (Umkm $record) => 
                        $record->status === 'pending' && 
                        (auth()->user()->isClient())
                    )
                    ->action(function (Umkm $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'alasan_reject' => $data['alasan_reject'],
                        ]);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                  Tables\Actions\DeleteBulkAction::make()
    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'pic_lapangan'])),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'pic_lapangan'])),

                 Action::make('exportExcel')
        ->label('Export Excel')
        ->icon('heroicon-o-document-arrow-down')
        ->color('success')
        ->action(function ($livewire) {

            $query = $livewire->getFilteredTableQuery();

            return \Maatwebsite\Excel\Facades\Excel::download(
                new \App\Exports\UmkmExport($query->get()),
                'umkm_terbranding_' . now()->format('Ymd_His') . '.xlsx'
            );
        }),

                Action::make('exportPdf')
                    ->label('Export PDF')
                    ->icon('heroicon-o-document-text')
                    ->color('danger')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Filter Status')
                            ->options([
                                '' => 'Semua Status',
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ]),
                        Forms\Components\Select::make('kota_id')
                            ->label('Filter Kota')
                            ->options(['' => 'Semua Kota'] + Kota::pluck('nama', 'id')->toArray()),
                    ])
                    ->action(function (array $data) {
                        $query = Umkm::with(['kota', 'submittedBy']);

                        if (!empty($data['status'])) {
                            $query->where('status', $data['status']);
                        }

                        if (!empty($data['kota_id'])) {
                            $query->where('kota_id', $data['kota_id']);
                        }

                        $umkms = $query->get();

                        $pdf = Pdf::loadView('exports.umkm-pdf', [
                            'umkms' => $umkms,
                            'title' => 'Data UMKM Branding Gerobak',
                            'date' => now()->format('d F Y'),
                        ]);

                        $pdf->setPaper('A4', 'landscape');

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            'data-umkm-' . now()->format('Y-m-d') . '.pdf'
                        );
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUmkms::route('/'),
            'create' => Pages\CreateUmkm::route('/create'),
            // 'view' => Pages\ViewUmkm::route('/{record}'),
            'edit' => Pages\EditUmkm::route('/{record}/edit'),
        ];
    }
}