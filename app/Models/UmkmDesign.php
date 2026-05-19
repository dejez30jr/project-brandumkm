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
    
    protected static function booted()
{
    static::updated(function ($umkmDesign) {
        // Cek jika status berubah menjadi 'revised' (sudah direvisi oleh desainer)
        if ($umkmDesign->isDirty('status') && $umkmDesign->status === 'revised') {
            
            $namaUsaha = $umkmDesign->umkm?->nama_usaha ?? 'UMKM';

            // Tentukan target user_id yang akan menerima notifikasi ini.
            // Sesuai gambar databasemu, id 5 atau 1 biasanya untuk reviewer/admin.
            // Kamu bisa pakai auth()->id() atau id spesifik penerima, contoh: 5
            $targetUserId = 5; 

            // Simpan langsung ke tabel notifikasis dengan field lengkap sesuai database kamu
            \App\Models\Notifikasi::create([
                'user_id'         => $targetUserId,
                'judul'           => 'Desain Telah Direvisi 🎨',
                'pesan'           => "Tim Desain telah memperbaiki desain untuk {$namaUsaha}. Silakan cek kembali untuk di-review.",
                'tipe'            => 'revised', // Sesuai kolom tipe varchar(255) di databasemu
                
                // MENGISI FIELD POLYMORPHIC YANG RELEVAN AGAR TIDAK ERROR GENERAL 1364
                'notifiable_type' => 'App\Models\UmkmDesign', 
                'notifiable_id'   => $umkmDesign->id,
                
                'is_read'         => 0, // Set default belum dibaca sesuai struktur databasemu
            ]);
        }
    });
}
}