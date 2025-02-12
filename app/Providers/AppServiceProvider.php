<?php

namespace App\Providers;

use App\Auth\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (!config('app.report_deprecations')) {
            // Both Laravel and Symfony try to force error_reporting(-1) with no way out, and it's super annoying.
            error_reporting(E_ALL & ~E_DEPRECATED);
        }

        $isDev = !$this->app->isProduction();

        // SSL termination means $request->getScheme() is always 'http', so prevent mixed content problems here.
        if (str_starts_with(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }

        Model::preventLazyLoading($isDev);
        Model::preventSilentlyDiscardingAttributes($isDev);

        // SuperAdmins bypass all auth checks
        Gate::before(fn(User $user) => $user->hasRole(Role::SuperAdmin) ?: null);
    }
}
