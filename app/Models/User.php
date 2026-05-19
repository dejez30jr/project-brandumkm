<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser {
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'kota_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ==
    // Cek apakah pengguna aktif untuk mengakses panel Filament
    // ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ==

    public function canAccessPanel( Panel $panel ): bool {
        return $this->is_active;
    }

    public function kota(): BelongsTo {
        return $this->belongsTo( Kota::class );
    }

    public function submittedUmkms(): HasMany {
        return $this->hasMany( Umkm::class, 'submitted_by' );
    }

    public function designs(): HasMany {
        return $this->hasMany( UmkmDesign::class, 'designer_id' );
    }

    public function notifikasis(): HasMany {
        return $this->hasMany( Notifikasi::class );
    }

    public function readNotifications() {
        return $this->belongsToMany( Notifikasi::class, 'notifikasi_user' )
        ->withPivot( 'read_at' )
        ->withTimestamps();
        // Tambahkan ini
    }

    // ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ==
    // Helper untuk memeriksa role pengguna
    // ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ===  ==

    public function isAdmin(): bool {
        return $this->role === 'admin';
    }

    public function isClient(): bool {
        return $this->role === 'client';
    }

    public function isDesign(): bool {
        return $this->role === 'design';
    }

    public function isPicLapangan(): bool {
        return $this->role === 'pic_lapangan';
    }

    public function isTeamPasang(): bool {
        return $this->role === 'team_pasang';
    }
}