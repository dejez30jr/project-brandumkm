<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Umkm extends Model
{
    protected $fillable = [
        // Data Pemilik
        'nama_pemilik',
        'nama_usaha',
        'alamat_usaha',
        'no_wa',
        'radius',
        'no_rekening',
        'nama_bank',
        'atas_nama_rekening',
        
        // Geotagging
        'latitude',
        'longitude',
        'sharelock_url',
        
        // Panel Depan - Width & Height
        'depan_atas_w',
        'depan_atas_h',
        'depan_tengah_w',
        'depan_tengah_h',
        'depan_bawah_w',
        'depan_bawah_h',
        
        // Panel Kanan - Width & Height
        'kanan_atas_w',
        'kanan_atas_h',
        'kanan_tengah_w',
        'kanan_tengah_h',
        'kanan_bawah_w',
        'kanan_bawah_h',
        
        // Panel Kiri - Width & Height
        'kiri_atas_w',
        'kiri_atas_h',
        'kiri_tengah_w',
        'kiri_tengah_h',
        'kiri_bawah_w',
        'kiri_bawah_h',
        
        // Panel M2 (auto-calculated)
        'depan_panel_atas_m2',
        'depan_panel_tengah_m2',
        'depan_panel_bawah_m2',
        'kanan_panel_atas_m2',
        'kanan_panel_tengah_m2',
        'kanan_panel_bawah_m2',
        'kiri_panel_atas_m2',
        'kiri_panel_tengah_m2',
        'kiri_panel_bawah_m2',
        
        // Total Area
        'total_area_branding',
        'memenuhi_kriteria',
        
        // Status & Approval
        'status',
        'alasan_reject',
        'approved_at',
        'approved_by',
        
        // Relasi
        'kota_id',
        'submitted_by',

        // foto
         'foto_depan',
    'foto_kanan',
    'foto_kiri',
    'foto_plang_alfamart',
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

    // Helper function untuk menghitung m2 dari W x H (cm)
    private function calculateM2(?float $width, ?float $height): float
    {
        if (!$width || !$height) return 0;
        return round(($width * $height) / 10000, 2); // cm² to m²
    }

    // Auto calculate sebelum save
    protected static function booted(): void
    {
        static::creating(function (Umkm $umkm) {
            if (empty($umkm->submitted_by)) {
                $umkm->submitted_by = auth()->id();
            }
        });

        static::saving(function (Umkm $umkm) {
            // Calculate M2 untuk setiap panel dari W x H
            $umkm->depan_panel_atas_m2 = $umkm->calculateM2($umkm->depan_atas_w, $umkm->depan_atas_h);
            $umkm->depan_panel_tengah_m2 = $umkm->calculateM2($umkm->depan_tengah_w, $umkm->depan_tengah_h);
            $umkm->depan_panel_bawah_m2 = $umkm->calculateM2($umkm->depan_bawah_w, $umkm->depan_bawah_h);
            
            $umkm->kanan_panel_atas_m2 = $umkm->calculateM2($umkm->kanan_atas_w, $umkm->kanan_atas_h);
            $umkm->kanan_panel_tengah_m2 = $umkm->calculateM2($umkm->kanan_tengah_w, $umkm->kanan_tengah_h);
            $umkm->kanan_panel_bawah_m2 = $umkm->calculateM2($umkm->kanan_bawah_w, $umkm->kanan_bawah_h);
            
            $umkm->kiri_panel_atas_m2 = $umkm->calculateM2($umkm->kiri_atas_w, $umkm->kiri_atas_h);
            $umkm->kiri_panel_tengah_m2 = $umkm->calculateM2($umkm->kiri_tengah_w, $umkm->kiri_tengah_h);
            $umkm->kiri_panel_bawah_m2 = $umkm->calculateM2($umkm->kiri_bawah_w, $umkm->kiri_bawah_h);
            
            // Calculate total area
            $total = $umkm->depan_panel_atas_m2 + $umkm->depan_panel_tengah_m2 + $umkm->depan_panel_bawah_m2
                   + $umkm->kanan_panel_atas_m2 + $umkm->kanan_panel_tengah_m2 + $umkm->kanan_panel_bawah_m2
                   + $umkm->kiri_panel_atas_m2 + $umkm->kiri_panel_tengah_m2 + $umkm->kiri_panel_bawah_m2;
            
            $umkm->total_area_branding = round($total, 2);
            $umkm->memenuhi_kriteria = $total >= 1.5; // Minimum 1.5 m2
        });
    }

    // Relationships
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