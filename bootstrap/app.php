<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;

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
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(function () {
        //
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
            if ($request->expectsJson() || collect($apiPaths)->contains(fn ($path) => $request->is($path))) {
                return response()->json([
                    'message' => 'Unauthenticated.',
                ], 401);
            }
        });
    })
    ->create();
