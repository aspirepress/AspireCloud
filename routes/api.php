<?php

// Note: api routes are not prefixed, i.e. all routes in here are from the root like web routes

use App\Http\Controllers\API\WpOrg\Core\BrowseHappyController;
use App\Http\Controllers\API\WpOrg\Core\ImportersController;
use App\Http\Controllers\API\WpOrg\Core\ServeHappyController;
use App\Http\Controllers\API\WpOrg\Core\StableCheckController;
use App\Http\Controllers\API\WpOrg\Plugins\PluginInformation_1_0_Controller;
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
        // core routes
        $router->group([
            'prefix' => 'core',
        ], function (Router $router) {
            // @formatter:off
            $router->any('/browse-happy/{version}', BrowseHappyController::class)->where(['version' => '1.1']);
            $router->any('/serve-happy/{version}', ServeHappyController::class)->where(['version' => '1.0']);
            $router->match(['get', 'post'], '/stable-check/{version}', StableCheckController::class)->where(['version' => '1.0']);
            $router->get('/importers/{version}', ImportersController::class)->where(['version' => '1.[01]']);
            /// Pass-through routes still going to .org
            $router->any('/checksums/{version}', PassThroughController::class)->where(['version' => '1.0']);
            $router->any('/credits/{version}', PassThroughController::class)->where(['version' => '1.[01]']);
            $router->any('/handbook/{version}', PassThroughController::class)->where(['version' => '1.0']);
            $router->any('/version-check/{version}', PassThroughController::class)->where(['version' => '1.[67]']);
        });
        // plugins routes
        $router->group([
            'prefix' => 'plugins',
        ], function (Router $router) {
            // Plugin information routes
            $router->get('/info/1.0/{slug}.json', PluginInformation_1_0_Controller::class);
            $router->get('/info/1.2', PluginInformation_1_2_Controller::class);
            // Plugins endpoints are implemented for version 1.2, older versions are still pass-through
            $router->any('/info/{version}', PassThroughController::class)->where(['version' => '1.[01]']);

           // Plugin update check routes
            $router->post('/update-check/1.1', PluginUpdateCheck_1_1_Controller::class);
            // Plugins endpoints are implemented for version 1.2, older versions are still pass-through
            $router->any('/update-check/{version}', PassThroughController::class)->where(['version' => '1.0']);
        });
        // secret key routes
        $router->group([
            'prefix' => 'secret-key',
        ], function (Router $router) {
            $router->get('/{version}', [SecretKeyController::class, 'index'])->where(['version' => '1.[01]']);
            $router->get('/{version}/salt', [SecretKeyController::class, 'salt'])->where(['version' => '1.1']);
        });
        // themes routes
        $router->group([
            'prefix' => 'themes',
        ], function (Router $router) {
            $router->get('/info/{version}', [ThemeController::class, 'info'])->where(['version' => '1.[012]']);
            $router->match(['get', 'post'], '/update-check/{version}', ThemeUpdatesController::class)->where(['version' => '1.[01]']);
        });
        // events routes
        $router->any('/events/{version}', PassThroughController::class)->where(['version' => '1.0']);
        // pattern routes
        $router->any('/patterns/{version}', PassThroughController::class)->where(['version' => '1.0']);
        // stats routes
        $router->group([
            'prefix' => 'stats',
        ], function (Router $router) {
            $router->any('/locale/{version}', PassThroughController::class)->where(['version' => '1.0']);
            $router->any('/mysql/{version}', PassThroughController::class)->where(['version' => '1.0']);
            $router->any('/php/{version}', PassThroughController::class)->where(['version' => '1.0']);
            $router->any('/plugin/{version}/downloads.php', PassThroughController::class)->where(['version' => '1.0']);
            $router->any('/plugin/{version}/{slug}', PassThroughController::class)->where(['version' => '1.0']);
            $router->any('/wordpress/{version}', PassThroughController::class)->where(['version' => '1.0']);
        });
        // translations routes
        $router->group([
            'prefix' => 'translations',
        ], function (Router $router) {
            $router->any('/core/{version}', PassThroughController::class)->where(['version' => '1.0']);
            $router->any('/plugins/{version}', PassThroughController::class)->where(['version' => '1.0']);
            $router->any('/themes/{version}', PassThroughController::class)->where(['version' => '1.0']);
        });
        // @formatter:on
    });

// Route::any('{path}', CatchAllController::class)->where('path', '.*');

    require __DIR__ . '/inc/admin-api.php';
    require __DIR__ . '/inc/download.php';
