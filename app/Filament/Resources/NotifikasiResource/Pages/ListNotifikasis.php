<?php

namespace App\Filament\Resources\NotifikasiResource\Pages;

use App\Filament\Resources\NotifikasiResource;
use App\Models\Notifikasi;
use Filament\Resources\Pages\ListRecords;

class ListNotifikasis extends ListRecords
{
    protected static string $resource = NotifikasiResource::class;

    /**
     * Hook yang dijalankan saat halaman list dibuka
     */
    public function mount(): void
    {
        parent::mount();

        $user = auth()->user();
        
        // Cari ID notifikasi yang belum pernah tercatat dibaca oleh user ini di tabel perantara
        $unreadIds = Notifikasi::whereDoesntHave('users', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->pluck('id');

        // Jika ada yang belum dibaca, masukkan ke tabel pivot notifikasi_user
        if ($unreadIds->isNotEmpty()) {
            // syncWithoutDetaching memastikan data lama tidak terhapus, hanya menambah yang baru
            $user->readNotifications()->syncWithoutDetaching($unreadIds);
        }
    }

    protected function getHeaderActions(): array
    {
        // Pastikan kosong agar tombol Create tidak muncul di header list
        return [];
    }
}