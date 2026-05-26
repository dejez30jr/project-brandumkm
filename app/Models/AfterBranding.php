<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $umkm_id
 * @property string $file_path
 * @property string|null $keterangan
 * @property int $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Umkm $umkm
 * @property-read \App\Models\User $uploadedBy
 * @method static Builder<static>|AfterBranding newModelQuery()
 * @method static Builder<static>|AfterBranding newQuery()
 * @method static Builder<static>|AfterBranding query()
 * @method static Builder<static>|AfterBranding whereCreatedAt($value)
 * @method static Builder<static>|AfterBranding whereFilePath($value)
 * @method static Builder<static>|AfterBranding whereId($value)
 * @method static Builder<static>|AfterBranding whereKeterangan($value)
 * @method static Builder<static>|AfterBranding whereUmkmId($value)
 * @method static Builder<static>|AfterBranding whereUpdatedAt($value)
 * @method static Builder<static>|AfterBranding whereUploadedBy($value)
 * @mixin \Eloquent
 */
class AfterBranding extends Model
{
    protected $fillable = [
        'umkm_id',
        'file_path',
        'keterangan',
        'uploaded_by',
    ];

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}