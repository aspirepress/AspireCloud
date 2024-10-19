<?php

// Note: api routes are not prefixed, i.e. all routes in here are from the root like web routes

use App\Http\Controllers\API\WpOrg\SecretKey\SecretKeyController;
use App\Http\Controllers\CatchAllController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

// https://codex.wordpress.org/WordPress.org_API

Route::prefix('/')
    ->group(function (Router $r) {
        $r->get('/secret-key/{version}', [SecretKeyController::class, 'index'])->where(['version' => '1.[01]']);
        $r->get('/secret-key/{version}/salt', [SecretKeyController::class, 'salt'])->where(['version' => '1.1']);

        $r->get('/stats/wordpress/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/stats/php/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/stats/mysql/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/stats/locale/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/stats/plugin/{version}/downloads.php', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/stats/plugin/{version}/{slug}', CatchAllController::class)->where(['version' => '1.0']);

        $r->get('/core/browse-happy/{version}', CatchAllController::class)->where(['version' => '1.1']);
        $r->get('/core/checksums/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/core/credits/{version}', CatchAllController::class)->where(['version' => '1.[01]']);
        $r->get('/core/handbook/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/core/importers/{version}', CatchAllController::class)->where(['version' => '1.[01]']);
        $r->get('/core/serve-happy/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/core/stable-check/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/core/version-check/{version}', CatchAllController::class)->where(['version' => '1.[67]']);

        $r->get('/translations/core/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/translations/plugins/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/translations/themes/{version}', CatchAllController::class)->where(['version' => '1.0']);

        $r->get('/themes/info/{version}', CatchAllController::class)->where(['version' => '1.[012]']);
        $r->get('/themes/update-check/{version}', CatchAllController::class)->where(['version' => '1.[01]']);

        $r->get('/plugins/info/{version}', CatchAllController::class)->where(['version' => '1.[012]']);
        $r->get('/plugins/update-check/{version}', CatchAllController::class)->where(['version' => '1.[01]']);

        $r->get('/patterns/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $r->get('/events/{version}', CatchAllController::class)->where(['version' => '1.0']);
    });

// Route::any('{path}', CatchAllController::class)->where('path', '.*');
