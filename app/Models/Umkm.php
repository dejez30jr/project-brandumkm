<?php

namespace App\Models;

use App\Models\AfterBranding;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property string $nama_pemilik
 * @property string $nama_usaha
 * @property string $alamat_usaha
 * @property string $no_wa
 * @property string|null $radius
 * @property string|null $no_rekening
 * @property string|null $nama_bank
 * @property string|null $atas_nama_rekening
 * @property string|null $jam_buka
 * @property string|null $jam_tutup
 * @property string|null $request_text
 * @property string|null $catatan
 * @property numeric|null $latitude
 * @property numeric|null $longitude
 * @property string|null $sharelock_url
 * @property numeric|null $depan_atas_w
 * @property numeric|null $depan_atas_h
 * @property numeric|null $depan_tengah_w
 * @property numeric|null $depan_tengah_h
 * @property numeric|null $depan_bawah_w
 * @property numeric|null $depan_bawah_h
 * @property numeric|null $kanan_atas_w
 * @property numeric|null $kanan_atas_h
 * @property numeric|null $kanan_tengah_w
 * @property numeric|null $kanan_tengah_h
 * @property numeric|null $kanan_bawah_w
 * @property numeric|null $kanan_bawah_h
 * @property numeric|null $kiri_atas_w
 * @property numeric|null $kiri_atas_h
 * @property numeric|null $kiri_tengah_w
 * @property numeric|null $kiri_tengah_h
 * @property numeric|null $kiri_bawah_w
 * @property numeric|null $kiri_bawah_h
 * @property numeric|null $depan_panel_atas_m2
 * @property numeric|null $depan_panel_tengah_m2
 * @property numeric|null $depan_panel_bawah_m2
 * @property numeric|null $kanan_panel_atas_m2
 * @property numeric|null $kanan_panel_tengah_m2
 * @property numeric|null $kanan_panel_bawah_m2
 * @property numeric|null $kiri_panel_atas_m2
 * @property numeric|null $kiri_panel_tengah_m2
 * @property numeric|null $kiri_panel_bawah_m2
 * @property numeric|null $total_area_branding
 * @property bool $memenuhi_kriteria
 * @property string $status
 * @property string|null $alasan_reject
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $foto_depan
 * @property string|null $foto_kanan
 * @property string|null $foto_kiri
 * @property string|null $foto_plang_alfamart
 * @property string|null $foto_tampak_jauh
 * @property string|null $video_validasi
 * @property string|null $design_final
 * @property string|null $design_gerobak_depan
 * @property string|null $design_gerobak_kiri
 * @property string|null $design_gerobak_kanan
 * @property int $kota_id
 * @property int $submitted_by
 * @property string|null $stiker_tampak_depan
 * @property string|null $stiker_tampak_kanan
 * @property string|null $stiker_tampak_kiri
 * @property string|null $foto_wide
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AfterBranding> $afterBrandings
 * @property-read int|null $after_brandings_count
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UmkmDesign> $designs
 * @property-read int|null $designs_count
 * @property-read \App\Models\Kota $kota
 * @property-read \App\Models\User $submittedBy
 * @property-read \App\Models\UmkmDesign|null $umkmDesign
 * @method static Builder<static>|Umkm newModelQuery()
 * @method static Builder<static>|Umkm newQuery()
 * @method static Builder<static>|Umkm query()
 * @method static Builder<static>|Umkm whereAlamatUsaha($value)
 * @method static Builder<static>|Umkm whereAlasanReject($value)
 * @method static Builder<static>|Umkm whereApprovedAt($value)
 * @method static Builder<static>|Umkm whereApprovedBy($value)
 * @method static Builder<static>|Umkm whereAtasNamaRekening($value)
 * @method static Builder<static>|Umkm whereCatatan($value)
 * @method static Builder<static>|Umkm whereCreatedAt($value)
 * @method static Builder<static>|Umkm whereDepanAtasH($value)
 * @method static Builder<static>|Umkm whereDepanAtasW($value)
 * @method static Builder<static>|Umkm whereDepanBawahH($value)
 * @method static Builder<static>|Umkm whereDepanBawahW($value)
 * @method static Builder<static>|Umkm whereDepanPanelAtasM2($value)
 * @method static Builder<static>|Umkm whereDepanPanelBawahM2($value)
 * @method static Builder<static>|Umkm whereDepanPanelTengahM2($value)
 * @method static Builder<static>|Umkm whereDepanTengahH($value)
 * @method static Builder<static>|Umkm whereDepanTengahW($value)
 * @method static Builder<static>|Umkm whereDesignFinal($value)
 * @method static Builder<static>|Umkm whereDesignGerobakDepan($value)
 * @method static Builder<static>|Umkm whereDesignGerobakKanan($value)
 * @method static Builder<static>|Umkm whereDesignGerobakKiri($value)
 * @method static Builder<static>|Umkm whereFotoDepan($value)
 * @method static Builder<static>|Umkm whereFotoKanan($value)
 * @method static Builder<static>|Umkm whereFotoKiri($value)
 * @method static Builder<static>|Umkm whereFotoPlangAlfamart($value)
 * @method static Builder<static>|Umkm whereFotoTampakJauh($value)
 * @method static Builder<static>|Umkm whereFotoWide($value)
 * @method static Builder<static>|Umkm whereId($value)
 * @method static Builder<static>|Umkm whereJamBuka($value)
 * @method static Builder<static>|Umkm whereJamTutup($value)
 * @method static Builder<static>|Umkm whereKananAtasH($value)
 * @method static Builder<static>|Umkm whereKananAtasW($value)
 * @method static Builder<static>|Umkm whereKananBawahH($value)
 * @method static Builder<static>|Umkm whereKananBawahW($value)
 * @method static Builder<static>|Umkm whereKananPanelAtasM2($value)
 * @method static Builder<static>|Umkm whereKananPanelBawahM2($value)
 * @method static Builder<static>|Umkm whereKananPanelTengahM2($value)
 * @method static Builder<static>|Umkm whereKananTengahH($value)
 * @method static Builder<static>|Umkm whereKananTengahW($value)
 * @method static Builder<static>|Umkm whereKiriAtasH($value)
 * @method static Builder<static>|Umkm whereKiriAtasW($value)
 * @method static Builder<static>|Umkm whereKiriBawahH($value)
 * @method static Builder<static>|Umkm whereKiriBawahW($value)
 * @method static Builder<static>|Umkm whereKiriPanelAtasM2($value)
 * @method static Builder<static>|Umkm whereKiriPanelBawahM2($value)
 * @method static Builder<static>|Umkm whereKiriPanelTengahM2($value)
 * @method static Builder<static>|Umkm whereKiriTengahH($value)
 * @method static Builder<static>|Umkm whereKiriTengahW($value)
 * @method static Builder<static>|Umkm whereKotaId($value)
 * @method static Builder<static>|Umkm whereLatitude($value)
 * @method static Builder<static>|Umkm whereLongitude($value)
 * @method static Builder<static>|Umkm whereMemenuhiKriteria($value)
 * @method static Builder<static>|Umkm whereNamaBank($value)
 * @method static Builder<static>|Umkm whereNamaPemilik($value)
 * @method static Builder<static>|Umkm whereNamaUsaha($value)
 * @method static Builder<static>|Umkm whereNoRekening($value)
 * @method static Builder<static>|Umkm whereNoWa($value)
 * @method static Builder<static>|Umkm whereRadius($value)
 * @method static Builder<static>|Umkm whereRequestText($value)
 * @method static Builder<static>|Umkm whereSharelockUrl($value)
 * @method static Builder<static>|Umkm whereStatus($value)
 * @method static Builder<static>|Umkm whereStikerTampakDepan($value)
 * @method static Builder<static>|Umkm whereStikerTampakKanan($value)
 * @method static Builder<static>|Umkm whereStikerTampakKiri($value)
 * @method static Builder<static>|Umkm whereSubmittedBy($value)
 * @method static Builder<static>|Umkm whereTotalAreaBranding($value)
 * @method static Builder<static>|Umkm whereUpdatedAt($value)
 * @method static Builder<static>|Umkm whereVideoValidasi($value)
 * @mixin \Eloquent
 */
class Umkm extends Model
{
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DESIGNING = 'designing';
    const STATUS_DESIGN_REVIEW = 'design_review';
    const STATUS_DESIGN_APPROVED = 'design_approved';
    const STATUS_REVISION_NEEDED = 'revision_needed';
    const STATUS_BRANDED = 'branded';

    const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
        self::STATUS_DESIGNING,
        self::STATUS_DESIGN_REVIEW,
        self::STATUS_DESIGN_APPROVED,
        self::STATUS_REVISION_NEEDED,
        self::STATUS_BRANDED,
    ];

    protected $fillable = [
        'nama_pemilik',
        'nama_usaha',
        'alamat_usaha',
        'no_wa',
        'radius',
        'no_rekening',
        'nama_bank',
        'atas_nama_rekening',
        'latitude',
        'longitude',
        'sharelock_url',
        'depan_atas_w',
        'depan_atas_h',
        'depan_tengah_w',
        'depan_tengah_h',
        'depan_bawah_w',
        'depan_bawah_h',
        'kanan_atas_w',
        'kanan_atas_h',
        'kanan_tengah_w',
        'kanan_tengah_h',
        'kanan_bawah_w',
        'kanan_bawah_h',
        'kiri_atas_w',
        'kiri_atas_h',
        'kiri_tengah_w',
        'kiri_tengah_h',
        'kiri_bawah_w',
        'kiri_bawah_h',
        'depan_panel_atas_m2',
        'depan_panel_tengah_m2',
        'depan_panel_bawah_m2',
        'kanan_panel_atas_m2',
        'kanan_panel_tengah_m2',
        'kanan_panel_bawah_m2',
        'kiri_panel_atas_m2',
        'kiri_panel_tengah_m2',
        'kiri_panel_bawah_m2',
        'total_area_branding',
        'memenuhi_kriteria',
        'status',
        'alasan_reject',
        'approved_at',
        'approved_by',
        'kota_id',
        'submitted_by',
        'foto_depan',
        'foto_kanan',
        'foto_kiri',
        'foto_plang_alfamart',
        'foto_tampak_jauh',
        'video_validasi',
        'jam_buka',
        'jam_tutup',
        'request_text',
        'catatan',
        'design_final',
        'design_gerobak_depan',
        'design_gerobak_kiri',
        'design_gerobak_kanan',
        'stiker_tampak_depan',
        'stiker_tampak_kanan',
        'stiker_tampak_kiri',
        'foto_wide',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'depan_atas_w' => 'decimal:2',
        'depan_atas_h' => 'decimal:2',
        'depan_tengah_w' => 'decimal:2',
        'depan_tengah_h' => 'decimal:2',
        'depan_bawah_w' => 'decimal:2',
        'depan_bawah_h' => 'decimal:2',
        'kanan_atas_w' => 'decimal:2',
        'kanan_atas_h' => 'decimal:2',
        'kanan_tengah_w' => 'decimal:2',
        'kanan_tengah_h' => 'decimal:2',
        'kanan_bawah_w' => 'decimal:2',
        'kanan_bawah_h' => 'decimal:2',
        'kiri_atas_w' => 'decimal:2',
        'kiri_atas_h' => 'decimal:2',
        'kiri_tengah_w' => 'decimal:2',
        'kiri_tengah_h' => 'decimal:2',
        'kiri_bawah_w' => 'decimal:2',
        'kiri_bawah_h' => 'decimal:2',
        'depan_panel_atas_m2' => 'decimal:2',
        'depan_panel_tengah_m2' => 'decimal:2',
        'depan_panel_bawah_m2' => 'decimal:2',
        'kanan_panel_atas_m2' => 'decimal:2',
        'kanan_panel_tengah_m2' => 'decimal:2',
        'kanan_panel_bawah_m2' => 'decimal:2',
        'kiri_panel_atas_m2' => 'decimal:2',
        'kiri_panel_tengah_m2' => 'decimal:2',
        'kiri_panel_bawah_m2' => 'decimal:2',
        'total_area_branding' => 'decimal:2',
        'memenuhi_kriteria' => 'boolean',
        'approved_at' => 'datetime',
    ];

    private function calculateM2(?float $width, ?float $height): float
    {
        if (!$width || !$height) return 0;
        return round(($width * $height) / 10000, 2);
    }

    protected static function booted(): void
    {
        static::creating(function (Umkm $umkm) {
            if (empty($umkm->submitted_by)) {
                $umkm->submitted_by = auth()->id();
            }
        });

        static::saving(function (Umkm $umkm) {
            $umkm->depan_panel_atas_m2 = $umkm->calculateM2($umkm->depan_atas_w, $umkm->depan_atas_h);
            $umkm->depan_panel_tengah_m2 = $umkm->calculateM2($umkm->depan_tengah_w, $umkm->depan_tengah_h);
            $umkm->depan_panel_bawah_m2 = $umkm->calculateM2($umkm->depan_bawah_w, $umkm->depan_bawah_h);

            $umkm->kanan_panel_atas_m2 = $umkm->calculateM2($umkm->kanan_atas_w, $umkm->kanan_atas_h);
            $umkm->kanan_panel_tengah_m2 = $umkm->calculateM2($umkm->kanan_tengah_w, $umkm->kanan_tengah_h);
            $umkm->kanan_panel_bawah_m2 = $umkm->calculateM2($umkm->kanan_bawah_w, $umkm->kanan_bawah_h);

            $umkm->kiri_panel_atas_m2 = $umkm->calculateM2($umkm->kiri_atas_w, $umkm->kiri_atas_h);
            $umkm->kiri_panel_tengah_m2 = $umkm->calculateM2($umkm->kiri_tengah_w, $umkm->kiri_tengah_h);
            $umkm->kiri_panel_bawah_m2 = $umkm->calculateM2($umkm->kiri_bawah_w, $umkm->kiri_bawah_h);

            $total = $umkm->depan_panel_atas_m2 + $umkm->depan_panel_tengah_m2 + $umkm->depan_panel_bawah_m2
                   + $umkm->kanan_panel_atas_m2 + $umkm->kanan_panel_tengah_m2 + $umkm->kanan_panel_bawah_m2
                   + $umkm->kiri_panel_atas_m2 + $umkm->kiri_panel_tengah_m2 + $umkm->kiri_panel_bawah_m2;

            $umkm->total_area_branding = round($total, 2);
            $umkm->memenuhi_kriteria = $total >= 1.5; // Minimum 1.5 m2
        });
    }

    public function kota(): BelongsTo
    {
        return $this->belongsTo(Kota::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function umkmDesign(): HasOne
    {
        return $this->hasOne(UmkmDesign::class, 'umkm_id')->latestOfMany();
    }

    public function designs(): HasMany
    {
        return $this->hasMany(UmkmDesign::class);
    }

    public function afterBrandings(): HasMany
    {
        return $this->hasMany(AfterBranding::class);
    }

    public function latestApprovedDesign()
    {
        return $this->designs()->where('status', 'approved')->latest()->first();
    }
}
