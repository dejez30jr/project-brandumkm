<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UmkmStiker extends Model
{
    protected $fillable = [
        'umkm_id',
        'stiker_tampak_depan',
        'stiker_tampak_kanan',
        'stiker_tampak_kiri',
        'foto_wide',
        'dipasang_oleh',
        'dipasang_at',
    ];

    protected $casts = [
        'dipasang_at' => 'datetime',
    ];

    public function umkm(): BelongsTo
    {
        return $this->belongsTo(Umkm::class);
    }

    public function dipasangOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dipasang_oleh');
    }
}
