<?php

// Note: api routes are not prefixed, i.e. all routes in here are from the root like web routes

use App\Http\Controllers\API\WpOrg\Core\BrowseHappyController;
use App\Http\Controllers\API\WpOrg\Core\ImportersController;
use App\Http\Controllers\API\WpOrg\Core\ServeHappyController;
use App\Http\Controllers\API\WpOrg\Core\StableCheckController;
use App\Http\Controllers\API\WpOrg\Plugins\PluginInformation_1_2_Controller;
use App\Http\Controllers\API\WpOrg\Plugins\PluginUpdateCheck_1_1_Controller;
use App\Http\Controllers\API\WpOrg\SecretKey\SecretKeyController;
use App\Http\Controllers\API\WpOrg\Themes\ThemeController;
use App\Http\Controllers\API\WpOrg\Themes\ThemeUpdatesController;
use App\Http\Controllers\PassThroughController;
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
        // @formatter:off

        $router->any('/core/browse-happy/{version}', BrowseHappyController::class)->where(['version' => '1.1']);
        $router->any('/core/serve-happy/{version}', ServeHappyController::class)->where(['version' => '1.0']);
        $router->match(['get', 'post'], '/core/stable-check/{version}', StableCheckController::class)->where(['version' => '1.0']);
        $router->get('/core/importers/{version}', ImportersController::class)->where(['version' => '1.[01]']);

        $router->get('/plugins/info/1.2', PluginInformation_1_2_Controller::class);
        $router->post('/plugins/update-check/1.1', PluginUpdateCheck_1_1_Controller::class);

        $router->get('/secret-key/{version}', [SecretKeyController::class, 'index'])->where(['version' => '1.[01]']);
        $router->get('/secret-key/{version}/salt', [SecretKeyController::class, 'salt'])->where(['version' => '1.1']);

        $router->get('/themes/info/{version}', [ThemeController::class, 'info'])->where(['version' => '1.[012]']);
        $router->match(['get', 'post'], '/themes/update-check/{version}', ThemeUpdatesController::class)->where(['version' => '1.[01]']);

        /// Pass-through routes still going to .org

        $router->any('/core/checksums/{version}', PassThroughController::class)->where(['version' => '1.0']);
        $router->any('/core/credits/{version}', PassThroughController::class)->where(['version' => '1.[01]']);
        $router->any('/core/handbook/{version}', PassThroughController::class)->where(['version' => '1.0']);
        $router->any('/core/version-check/{version}', PassThroughController::class)->where(['version' => '1.[67]']);

        $router->any('/events/{version}', PassThroughController::class)->where(['version' => '1.0']);

        $router->any('/patterns/{version}', PassThroughController::class)->where(['version' => '1.0']);

        // /plugins endpoints are implemented for version 1.2, older versions are still pass-through
        $router->any('/plugins/info/{version}', PassThroughController::class)->where(['version' => '1.[01]']);
        $router->any('/plugins/update-check/{version}', PassThroughController::class)->where(['version' => '1.0']);

        $router->any('/stats/locale/{version}', PassThroughController::class)->where(['version' => '1.0']);
        $router->any('/stats/mysql/{version}', PassThroughController::class)->where(['version' => '1.0']);
        $router->any('/stats/php/{version}', PassThroughController::class)->where(['version' => '1.0']);
        $router->any('/stats/plugin/{version}/downloads.php', PassThroughController::class)->where(['version' => '1.0']);
        $router->any('/stats/plugin/{version}/{slug}', PassThroughController::class)->where(['version' => '1.0']);
        $router->any('/stats/wordpress/{version}', PassThroughController::class)->where(['version' => '1.0']);

        $router->any('/translations/core/{version}', PassThroughController::class)->where(['version' => '1.0']);
        $router->any('/translations/plugins/{version}', PassThroughController::class)->where(['version' => '1.0']);
        $router->any('/translations/themes/{version}', PassThroughController::class)->where(['version' => '1.0']);

        // @formatter:on
    });

// Route::any('{path}', CatchAllController::class)->where('path', '.*');

require __DIR__ . '/inc/admin-api.php';
require __DIR__ . '/inc/download.php';
