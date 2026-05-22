<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UmkmDesign extends Model
{
    protected $fillable = [
        'umkm_id',
        'designer_id',
        'file_path',
        'status',
        'catatan_revisi',
        'versi',
        'approved_at',
        'approved_by',
        'gerobak_depan',
        'gerobak_kiri',
        'gerobak_kanan',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];
    
// =====================================================================
// Relasi Eloquent
// =====================================================================
    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class);
    }

    public function designer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'designer_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
    
}