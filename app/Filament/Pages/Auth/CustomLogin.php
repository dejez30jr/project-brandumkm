<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class CustomLogin extends BaseLogin
{
    /**
     * @return array<string, mixed>
     */
    protected function getLayoutAttributes(): array
    {
        return [
            'class' => 'fi-simple-main w-full max-w-md',
            // Kita suntik CSS global murni di sini agar semua device (terutama mobile) tunduk
            'style' => '
                display: block !important;
                padding: 24px !important; 
                margin: 0 auto !important;
                box-sizing: border-box !important;
            ',
        ];
    }

    // Trik Tambahan: Suntik CSS internal langsung ke halaman via Sub-Heading atau Judul
    public function getHeading(): string | Htmlable
    {
        return new \Illuminate\Support\HtmlString('
            <style>
                /* Paksa layout utama di mobile agar punya jarak aman dan tidak mepet */
                @media (max-width: 640px) {
                    .fi-simple-layout {
                        border-radius: 30%;
                        padding: 20px !important;
                        display: flex !important;
                        align-items: center !important;
                        justify-content: center !important;
                        min-height: 100vh !important;
                    }
                    .fi-simple-main {
                        padding: 16px !important;
                        width: 100% !important;
                    }
                }
            </style>
            Sign in
        ');
    }
}