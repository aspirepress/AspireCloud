<?php
declare(strict_types=1);

use App\Http\Middleware\TrustProxies;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Validation\ValidationException;
use Illuminate\View\Middleware\ShareErrorsFromSession;

$apiPaths = [
    'secret-key/*',
    'stats/*',
    'core/*',
    'translations/*',
    'themes/*',
    'plugins/*',
    'patterns/*',
    'events/*',
];

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // https://laravel.com/docs/11.x/middleware#manually-managing-laravels-default-middleware-groups
        $middleware->group('web', [
            TrustProxies::class, // overridden with $proxies = "*"
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            ValidateCsrfToken::class,
            SubstituteBindings::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
        ]);

        $middleware->group('api', [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // 'throttle:api',
            SubstituteBindings::class,
            // CacheApiResponse::class // replaced with laravels cache.headers in api.php
        ]);

        $middleware->alias([
            // https://spatie.be/docs/laravel-permission/v6/basic-usage/middleware
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,

            'auth.optional' => App\Http\Middleware\AuthOptional::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) use ($apiPaths) {
        /**
         * This is a workaround to avoid the redirection to the login page when the api routes are not authenticated
         *
         * When the sanctum middleware is applied to the api routes and the request is not authenticated
         * a redirection to the login is triggered due our api endpoints doesn't have a prefix
         * and the request is trated as a regular web request.
         *
         * For this reason, we need to force a return as a json response instead of a redirection
         * on the api endpoints
         * */
        $exceptions->render(function (AuthenticationException $e, Request $request) use ($apiPaths) {
            if ($request->expectsJson() || collect($apiPaths)->contains(fn($path) => $request->is($path))) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        /**
         * We do not use HTML forms, so all validation errors are json with status 422, regardless of Accept:
         * If you need Laravel's default behavior back, use a middleware to send back the redirect.
         */
        // XXX WTF [chuck 2025-09-19] Disabled this, because tests fail with a type error, but ONLY in local dev,
        //         they work fine in CI.  Words cannot express the loathing I feel toward Laravel right now.
        //         This only affects requests from a browser, so it doesn't affect anything to remove it.
        // $exceptions->shouldRenderJsonWhen(fn(Request $request, ValidationException $e) => true);
    })
    ->create();
