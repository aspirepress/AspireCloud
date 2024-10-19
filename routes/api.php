<?php

// Note: api routes are not prefixed, i.e. all routes in here are from the root like web routes

use App\Http\Controllers\CatchAllController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

// https://codex.wordpress.org/WordPress.org_API

Route::prefix('/')
    ->group(function (Router $r) {
        $r->get('/secret-key/{version}', CatchAllController::class);
        $r->get('/secret-key/{version}/salt', CatchAllController::class);

        $r->get('/stats/wordpress/{version}', CatchAllController::class);
        $r->get('/stats/php/{version}', CatchAllController::class);
        $r->get('/stats/mysql/{version}', CatchAllController::class);
        $r->get('/stats/locale/{version}', CatchAllController::class);
        $r->get('/stats/plugin/{version}/downloads.php', CatchAllController::class);
        $r->get('/stats/plugin/{version}/{slug}', CatchAllController::class);

        $r->get('/core/browse-happy/{version}', CatchAllController::class);
        $r->get('/core/checksums/{version}', CatchAllController::class);
        $r->get('/core/credits/{version}', CatchAllController::class);
        $r->get('/core/handbook/{version}', CatchAllController::class);
        $r->get('/core/importers/{version}', CatchAllController::class);
        $r->get('/core/serve-happy/{version}', CatchAllController::class);
        $r->get('/core/stable-check/{version}', CatchAllController::class);
        $r->get('/core/version-check/{version}', CatchAllController::class);

        $r->get('/translations/core/{version}', CatchAllController::class);
        $r->get('/translations/plugins/{version}', CatchAllController::class);
        $r->get('/translations/themes/{version}', CatchAllController::class);

        $r->get('/themes/info/{version}', CatchAllController::class);
        $r->get('/themes/update-check/{version}', CatchAllController::class);

        $r->get('/plugins/info/{version}', CatchAllController::class);
        $r->get('/plugins/update-check/{version}', CatchAllController::class);

        $r->get('/patterns/{version}', CatchAllController::class);
        $r->get('/events/{version}', CatchAllController::class);
    })->where(['version' => '[0-9]+\.[0-9]+']);

Route::any('{path}', CatchAllController::class)->where('path', '.*');
