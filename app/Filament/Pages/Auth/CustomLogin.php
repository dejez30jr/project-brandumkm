<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;

class CustomLogin extends BaseLogin
{
    protected function getLayoutAttributes(): array
    {
        return [
            'class' => 'fi-simple-main w-full max-w-md',
            'style' => '
                display: block !important;
                padding: 24px !important; 
                margin: 0 auto !important;
                box-sizing: border-box !important;
            ',
        ];
    }

    public function getHeading(): string | Htmlable
    {
        return new \Illuminate\Support\HtmlString('
            <style>
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

    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        $response = parent::authenticate();

        // Single session enforcement for client role
        $user = auth()->user();
        if ($user && $user->role === 'client') {
            DB::table('sessions')
                ->where('user_id', $user->id)
                ->where('id', '!=', session()->getId())
                ->delete();
        }

        return $response;
    }
}