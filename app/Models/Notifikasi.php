<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string $judul
 * @property string $pesan
 * @property string $tipe
 * @property string $notifiable_type
 * @property int $notifiable_id
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $notifiable
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static Builder<static>|Notifikasi newModelQuery()
 * @method static Builder<static>|Notifikasi newQuery()
 * @method static Builder<static>|Notifikasi query()
 * @method static Builder<static>|Notifikasi whereCreatedAt($value)
 * @method static Builder<static>|Notifikasi whereId($value)
 * @method static Builder<static>|Notifikasi whereIsRead($value)
 * @method static Builder<static>|Notifikasi whereJudul($value)
 * @method static Builder<static>|Notifikasi whereNotifiableId($value)
 * @method static Builder<static>|Notifikasi whereNotifiableType($value)
 * @method static Builder<static>|Notifikasi wherePesan($value)
 * @method static Builder<static>|Notifikasi whereTipe($value)
 * @method static Builder<static>|Notifikasi whereUpdatedAt($value)
 * @method static Builder<static>|Notifikasi whereUserId($value)
 * @mixin \Eloquent
 */
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

    public function users()
    {
        return $this->belongsToMany(User::class, 'notifikasi_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }
}
