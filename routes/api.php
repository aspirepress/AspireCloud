<?php

// Note: api routes are not prefixed, i.e. all routes in here are from the root like web routes

use App\Http\Controllers\API\WpOrg\Core\ImportersController;
use App\Http\Controllers\API\WpOrg\Plugins\PluginInformation_1_2_Controller;
use App\Http\Controllers\API\WpOrg\Plugins\PluginUpdateCheck_1_1_Controller;
use App\Http\Controllers\API\WpOrg\SecretKey\SecretKeyController;
use App\Http\Controllers\API\WpOrg\Themes\ThemeController;
use App\Http\Controllers\API\WpOrg\Themes\ThemeUpdatesController;
use App\Http\Controllers\CatchAllController;
use App\Http\Middleware\NormalizeWpOrgRequest;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

// https://codex.wordpress.org/WordPress.org_API

Route::prefix('/')
    ->middleware([
        'auth.optional:sanctum',
        NormalizeWpOrgRequest::class,
        'cache.headers:public;s_maxage=300,etag', // for the CDN's benefit: the WP user agent does not cache at all.
    ])
    ->group(function (Router $router) {
        $router->get('/secret-key/{version}', [SecretKeyController::class, 'index'])->where(['version' => '1.[01]']);
        $router->get('/secret-key/{version}/salt', [SecretKeyController::class, 'salt'])->where(['version' => '1.1']);

        $router->get('/stats/wordpress/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/stats/php/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/stats/mysql/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/stats/locale/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/stats/plugin/{version}/downloads.php', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/stats/plugin/{version}/{slug}', CatchAllController::class)->where(['version' => '1.0']);

        $router->get('/core/browse-happy/{version}', CatchAllController::class)->where(['version' => '1.1']);
        $router->get('/core/checksums/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/core/credits/{version}', CatchAllController::class)->where(['version' => '1.[01]']);
        $router->get('/core/handbook/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/core/importers/{version}', ImportersController::class)->where(['version' => '1.[01]']);
        $router->get('/core/serve-happy/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/core/stable-check/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/core/version-check/{version}', CatchAllController::class)->where(['version' => '1.[67]']);

        $router->get('/translations/core/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/translations/plugins/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/translations/themes/{version}', CatchAllController::class)->where(['version' => '1.0']);

        $router->get('/themes/info/{version}', [ThemeController::class, 'info'])->where(['version' => '1.[012]']);
        $router->match(['get', 'post'], '/themes/update-check/{version}', ThemeUpdatesController::class)->where(
            ['version' => '1.[01]'],
        );

        $router->get('/plugins/info/1.2', PluginInformation_1_2_Controller::class);
        $router->get('/plugins/info/{version}', CatchAllController::class)->where(['version' => '1.[01]']);

        $router->post('/plugins/update-check/1.1', PluginUpdateCheck_1_1_Controller::class);
        $router->get('/plugins/update-check/{version}', CatchAllController::class)->where(['version' => '1.0']);

        $router->get('/patterns/{version}', CatchAllController::class)->where(['version' => '1.0']);
        $router->get('/events/{version}', CatchAllController::class)->where(['version' => '1.0']);
    });

// Route::any('{path}', CatchAllController::class)->where('path', '.*');

require __DIR__ . '/inc/admin-api.php';
require __DIR__ . '/inc/download.php';
