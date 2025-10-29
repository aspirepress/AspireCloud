<?php
declare(strict_types=1);

// This one file should contain all boot-time actions relevant to the app.
// There should be no need to create more ServiceProviders, unless for an actually separable concern.

namespace App\Providers;

use App\Auth\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Put only DI container bindings here, if and when they're needed.
    }

    public function boot(): void
    {
        if (!config('app.report_deprecations')) {
            // Both Laravel and Symfony try to force error_reporting(-1) with no way out, and it's super annoying.
            error_reporting(E_ALL & ~E_DEPRECATED);
        }

        // SSL termination means $request->getScheme() is always 'http', so prevent mixed content problems here.
        if (str_starts_with(config('app.url'), 'https')) {
            URL::forceScheme('https');
        }

        $isDev = !$this->app->isProduction();
        Model::preventLazyLoading($isDev);
        Model::preventSilentlyDiscardingAttributes($isDev);

        // SuperAdmins bypass all auth checks
        Gate::before(fn(User $user) => $user->hasRole(Role::SuperAdmin) ?: null);

        // make a vague stab at functional abstraction ;)
        $this->bootRoutes();
    }

    private function bootRoutes(): void
    {
        Route::pattern('slug', '[-_A-Za-z0-9]+'); // underscore is uncommon, but some assets do use it.
    }
}
