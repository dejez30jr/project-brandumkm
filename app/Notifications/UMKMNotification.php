<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

// Implementasi ShouldQueue agar tidak membuat website lemot
class UMKMNotification extends Notification implements ShouldQueue 
{
    use Queueable;

    protected $judul;
    protected $pesan;

    public function __construct($judul, $pesan) 
    { 
        $this->judul = $judul;
        $this->pesan = $pesan; 
    }

    public function via($notifiable) 
    { 
        // Hanya kirim via email, tidak via database (tabel notifications tidak digunakan)
        return ['mail']; 
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject($this->judul)
            ->line($this->pesan)
            ->action('Buka Aplikasi', url('/admin'));
    }

    // Agar muncul di notifikasi bawaan Filament
    public function toArray($notifiable)
    {
        return [
            'title' => $this->judul,
            'body' => $this->pesan,
        ];
    }
}