<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $isDev = !$this->app->isProduction();
        Model::preventLazyLoading($isDev);
        Model::preventSilentlyDiscardingAttributes($isDev);
    }
}
