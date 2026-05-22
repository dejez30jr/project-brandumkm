<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notifikasi extends Model
{
    protected $table = 'notifikasis';
    protected $fillable = [
        'user_id',
        'judul',
        'pesan',
        'tipe',
        'notifiable_type',
        'notifiable_id',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    // Di dalam class Notifikasi
public function users()
{
    return $this->belongsToMany(User::class, 'notifikasi_user')
                ->withPivot('read_at')
                ->withTimestamps();
}
}