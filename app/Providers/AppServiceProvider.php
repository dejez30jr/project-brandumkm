<?php

namespace App\Providers;

use App\Models\Umkm;
use App\Models\UmkmDesign;
use App\Observers\UmkmObserver;
use App\Observers\UmkmDesignObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Umkm::observe(UmkmObserver::class);
        UmkmDesign::observe(UmkmDesignObserver::class);
    }
}