<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $umkm_id
 * @property int $designer_id
 * @property string $file_path
 * @property string|null $gerobak_depan
 * @property string|null $gerobak_kiri
 * @property string|null $gerobak_kanan
 * @property string $status
 * @property string|null $catatan_revisi
 * @property int $versi
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\User $designer
 * @property-read \App\Models\Umkm $umkm
 * @method static Builder<static>|UmkmDesign newModelQuery()
 * @method static Builder<static>|UmkmDesign newQuery()
 * @method static Builder<static>|UmkmDesign query()
 * @method static Builder<static>|UmkmDesign whereApprovedAt($value)
 * @method static Builder<static>|UmkmDesign whereApprovedBy($value)
 * @method static Builder<static>|UmkmDesign whereCatatanRevisi($value)
 * @method static Builder<static>|UmkmDesign whereCreatedAt($value)
 * @method static Builder<static>|UmkmDesign whereDesignerId($value)
 * @method static Builder<static>|UmkmDesign whereFilePath($value)
 * @method static Builder<static>|UmkmDesign whereGerobakDepan($value)
 * @method static Builder<static>|UmkmDesign whereGerobakKanan($value)
 * @method static Builder<static>|UmkmDesign whereGerobakKiri($value)
 * @method static Builder<static>|UmkmDesign whereId($value)
 * @method static Builder<static>|UmkmDesign whereStatus($value)
 * @method static Builder<static>|UmkmDesign whereUmkmId($value)
 * @method static Builder<static>|UmkmDesign whereUpdatedAt($value)
 * @method static Builder<static>|UmkmDesign whereVersi($value)
 * @mixin \Eloquent
 */
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
