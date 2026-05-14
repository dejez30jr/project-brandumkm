<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
