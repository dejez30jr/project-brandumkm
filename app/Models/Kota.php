<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Umkm> $umkms
 * @property-read int|null $umkms_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static Builder<static>|Kota newModelQuery()
 * @method static Builder<static>|Kota newQuery()
 * @method static Builder<static>|Kota query()
 * @method static Builder<static>|Kota whereCreatedAt($value)
 * @method static Builder<static>|Kota whereId($value)
 * @method static Builder<static>|Kota whereNama($value)
 * @method static Builder<static>|Kota whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Kota extends Model
{
    protected $fillable = ['nama'];

    public function umkms(): HasMany
    {
        return $this->hasMany(Umkm::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
