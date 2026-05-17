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
use Filament\Tables;
use Filament\Tables\Actions\Action;
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

    // Badge notifikasi
    public static function getNavigationBadge(): ?string
    {
        $count = Umkm::where('status', 'pending')->count();
        return $count > 0 ? (string) $count : null;
    }

    // Warna badge
    public static function getNavigationBadgeColor(): ?string
    {
        $count = Umkm::where('status', 'pending')->count();
        return $count > 5 ? 'danger' : 'warning'; // Merah jika > 5, kuning jika <= 5
    }

    // Tooltip badge (opsional)
    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'UMKM menunggu persetujuan';
    }
    

    //  tombol create
  public static function canCreate(): bool
{
    $user = auth()->user();
    return $user && in_array($user->role, ['pic_lapangan']);
}
// tombol edit
public static function canEdit($record): bool
{
    $user = auth()->user();
    return in_array($user->role, ['pic_lapangan']);
}

// tombol delete
public static function canDelete($record): bool
{
    $user = auth()->user();
    return in_array($user->role, ['admin', 'pic_lapangan']);
}

    public static function getTableQuery(): \Illuminate\Database\Eloquent\Builder
{
    $query = parent::getTableQuery();

    // Jika user login adalah client, filter data sesuai user_id
    if (auth()->user()->isClient()) {
        $query->where('client_id', auth()->id());
    }

    return $query;
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
                            ->required(),

                        Forms\Components\TextInput::make('radius')
                            ->label('Radius dari Alfamart')
                            ->placeholder('Contoh:50,20...'),

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
                            ->required(),

                        Forms\Components\TextInput::make('nama_bank')
                            ->label('Nama Bank')
                            ->required()
                            ->placeholder('Bca,Bni,Mandiri...'),

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
                         // Auto-detect lokasi otomatis saat form dibuka (tanpa tombol)
        Forms\Components\Placeholder::make('auto_location_trigger')
            ->label('')
            ->content(new \Illuminate\Support\HtmlString(
                '<div 
                    x-data="{ loading: true, success: false, error: null }"
                    x-init="
                        setTimeout(() => {
                            if (navigator.geolocation) {
                                navigator.geolocation.getCurrentPosition(
                                    function(position) {
                                        $wire.set(\'data.latitude\', position.coords.latitude.toFixed(8));
                                        $wire.set(\'data.longitude\', position.coords.longitude.toFixed(8));
                                        $wire.set(\'data.sharelock_url\', \'https://www.google.com/maps?q=\' + position.coords.latitude + \',\' + position.coords.longitude);
                                        loading = false;
                                        success = true;
                                    },
                                    function(err) {
                                        loading = false;
                                        if (err.code === 1) {
                                            error = \'Izin lokasi ditolak. Silakan aktifkan GPS di browser.\';
                                        } else if (err.code === 2) {
                                            error = \'Lokasi tidak tersedia.\';
                                        } else {
                                            error = \'Timeout mengambil lokasi.\';
                                        }
                                    },
                                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                                );
                            } else {
                                loading = false;
                                error = \'Browser tidak mendukung GPS\';
                            }
                        }, 500);
                    "
                >
                    <div x-show="loading" class="flex items-center gap-2 text-primary-600 p-3 bg-primary-50 dark:bg-primary-900/20 rounded-lg mb-4">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Mengambil lokasi otomatis...</span>
                    </div>
                    <div x-show="success" class="flex items-center gap-2 text-success-600 p-3 bg-success-50 dark:bg-success-900/20 rounded-lg mb-4">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span>Lokasi berhasil diambil!</span>
                    </div>
                    <div x-show="error" class="flex items-center gap-2 text-danger-600 p-3 bg-danger-50 dark:bg-danger-900/20 rounded-lg mb-4">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                        <span x-text="error"></span>
                    </div>
                </div>'
            ))
            ->columnSpanFull(),

        Forms\Components\Grid::make(2)
            ->schema([
                Forms\Components\TextInput::make('latitude')
                    ->label('Latitude')
                    ->numeric()
                    ->step(0.00000001)
                    ->placeholder('Mengambil lokasi...')
                    ->readOnly()
                    ->live(),
                    
                Forms\Components\TextInput::make('longitude')
                    ->label('Longitude')
                    ->numeric()
                    ->step(0.00000001)
                    ->placeholder('Mengambil lokasi...')
                    ->readOnly()
                    ->live(),
            ]),

        Forms\Components\TextInput::make('sharelock_url')
            ->label('Google Maps URL')
            ->required()
            ->url()
            ->placeholder('Otomatis terisi...')
            ->readOnly()
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
            ->content(new \Illuminate\Support\HtmlString(
                '<span class="text-warning-600 font-semibold">Minimum total area branding: 1.5 M²</span>'
            )),


        // ========== PANEL DEPAN ==========
        Forms\Components\Section::make('Panel Depan')
            ->description(function (Get $get): string {
                $total = self::calculatePanelTotal($get, 'depan');
                return "{$total} M²";
            })
            ->schema([
                // Bagian Atas
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('depan_atas_label')
                            ->label('')
                            ->content('Bagian Atas'),
                        Forms\Components\TextInput::make('depan_atas_w')
                            ->required()
                            ->label('W (cm)')
                            ->numeric()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('depan_atas_h')
                            ->label('H (cm)')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('depan_panel_atas_m2'),

                // Bagian Tengah
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('depan_tengah_label')
                            ->label('')
                            ->content('Bagian Tengah'),
                        Forms\Components\TextInput::make('depan_tengah_w')
                            ->label('W (cm)')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('depan_tengah_h')
                            ->label('H (cm)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('depan_panel_tengah_m2'),

                // Bagian Bawah
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('depan_bawah_label')
                            ->label('')
                            ->content('Bagian Bawah'),
                        Forms\Components\TextInput::make('depan_bawah_w')
                            ->label('W (cm)')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('depan_bawah_h')
                            ->label('H (cm)')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('depan_panel_bawah_m2'),
            ])
            ->collapsible()
            ->extraAttributes(['class' => 'border-l-4 border-l-primary-500']),

        // ========== PANEL KANAN ==========
        Forms\Components\Section::make('Panel Kanan')
            ->description(function (Get $get): string {
                $total = self::calculatePanelTotal($get, 'kanan');
                return "{$total} M²";
            })
            ->schema([
                // Bagian Atas
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('kanan_atas_label')
                            ->label('')
                            ->content('Bagian Atas'),
                        Forms\Components\TextInput::make('kanan_atas_w')
                            ->label('W (cm)')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('kanan_atas_h')
                            ->label('H (cm)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('kanan_panel_atas_m2'),

                // Bagian Tengah
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('kanan_tengah_label')
                            ->label('')
                            ->content('Bagian Tengah'),
                        Forms\Components\TextInput::make('kanan_tengah_w')
                            ->label('W (cm)')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('kanan_tengah_h')
                            ->label('H (cm)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('kanan_panel_tengah_m2'),

                // Bagian Bawah
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('kanan_bawah_label')
                            ->label('')
                            ->content('Bagian Bawah'),
                        Forms\Components\TextInput::make('kanan_bawah_w')
                            ->label('W (cm)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('kanan_bawah_h')
                            ->label('H (cm)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('kanan_panel_bawah_m2'),
            ])
            ->collapsible()
            ->extraAttributes(['class' => 'border-l-4 border-l-success-500']),

        // ========== PANEL KIRI ==========
        Forms\Components\Section::make('Panel Kiri')
            ->description(function (Get $get): string {
                $total = self::calculatePanelTotal($get, 'kiri');
                return "{$total} M²";
            })
            ->schema([
                // Bagian Atas
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('kiri_atas_label')
                            ->label('')
                            ->content('Bagian Atas'),
                        Forms\Components\TextInput::make('kiri_atas_w')
                            ->label('W (cm)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('kiri_atas_h')
                            ->label('H (cm)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('kiri_panel_atas_m2'),

                // Bagian Tengah
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('kiri_tengah_label')
                            ->label('')
                            ->content('Bagian Tengah'),
                        Forms\Components\TextInput::make('kiri_tengah_w')
                            ->label('W (cm)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('kiri_tengah_h')
                            ->label('H (cm)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('kiri_panel_tengah_m2'),

                // Bagian Bawah
                Forms\Components\Grid::make(3)
                    ->schema([
                        Forms\Components\Placeholder::make('kiri_bawah_label')
                            ->label('')
                            ->content('Bagian Bawah'),
                        Forms\Components\TextInput::make('kiri_bawah_w')
                            ->label('W (cm)')
                            ->required()
                            ->numeric()
                            ->live(onBlur: true),
                        Forms\Components\TextInput::make('kiri_bawah_h')
                            ->label('H (cm)')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true),
                    ]),
                Forms\Components\Hidden::make('kiri_panel_bawah_m2'),
            ])
            ->collapsible()
            ->extraAttributes(['class' => 'border-l-4 border-l-warning-500']),
              // Total Area Branding
        Forms\Components\Placeholder::make('total_area_display')
            ->label('Total Area Branding')
            ->content(function (Get $get): \Illuminate\Support\HtmlString {
                $depan = self::calculatePanelTotal($get, 'depan');
                $kanan = self::calculatePanelTotal($get, 'kanan');
                $kiri = self::calculatePanelTotal($get, 'kiri');
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
    ->directory('umkm-fotos')
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
    ->directory('umkm-fotos')
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
    ->directory('umkm-fotos')
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
    ->directory('umkm-fotos')
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
                    ->label('UPLOAD VIDEO (MP4)')
                    ->disk('public')
                    ->directory('umkm-videos')
                    ->visibility('public')
                    ->acceptedFileTypes(['video/mp4', 'video/quicktime', 'video/x-msvideo'])
                    ->maxSize(102400) // 100MB max
                    ->placeholder('Klik untuk upload video'),
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
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ]),
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

                \Filament\Infolists\Components\TextEntry::make('total_area_branding')
                    ->label('Total Area Branding'),

                \Filament\Infolists\Components\TextEntry::make('depan_panel_atas_m2')
                    ->label('Depan Atas'),

                \Filament\Infolists\Components\TextEntry::make('depan_panel_tengah_m2')
                    ->label('Depan Tengah'),

                \Filament\Infolists\Components\TextEntry::make('depan_panel_bawah_m2')
                    ->label('Depan Bawah'),

                \Filament\Infolists\Components\TextEntry::make('kanan_panel_atas_m2')
                    ->label('Kanan Atas'),

                \Filament\Infolists\Components\TextEntry::make('kanan_panel_tengah_m2')
                    ->label('Kanan Tengah'),

                \Filament\Infolists\Components\TextEntry::make('kanan_panel_bawah_m2')
                    ->label('Kanan Bawah'),

                \Filament\Infolists\Components\TextEntry::make('kiri_panel_atas_m2')
                    ->label('Kiri Atas'),

                \Filament\Infolists\Components\TextEntry::make('kiri_panel_tengah_m2')
                    ->label('Kiri Tengah'),

                \Filament\Infolists\Components\TextEntry::make('kiri_panel_bawah_m2')
                    ->label('Kiri Bawah'),

            ])
            ->columns(3),
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
    ->visible(fn () => in_array(auth()->user()?->role, ['admin', 'pic_lapangan'])),
                
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Umkm $record) => 
                        $record->status === 'pending' && 
                        (auth()->user()->isClient() || auth()->user()->isPicLapangan())
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
                        $status = $data['status'] ?: null;
                        $kotaId = $data['kota_id'] ?: null;

                        return Excel::download(
                            new UmkmExport($status, $kotaId),
                            'data-umkm-' . now()->format('Y-m-d') . '.xlsx'
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