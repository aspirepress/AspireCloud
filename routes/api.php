<?php

use App\Http\Controllers\API\Elastic\ElasticSearchController;
use App\Http\Controllers\API\FAIR\Packages\PackageInformationController;
use App\Http\Controllers\API\Metrics\MetricsController;
use App\Http\Controllers\API\WpOrg\Core\BrowseHappyController;
use App\Http\Controllers\API\WpOrg\Core\ImportersController;
use App\Http\Controllers\API\WpOrg\Core\ServeHappyController;
use App\Http\Controllers\API\WpOrg\Core\StableCheckController;
use App\Http\Controllers\API\WpOrg\Export\ExportController;
use App\Http\Controllers\API\WpOrg\Plugins\PluginInformation_1_0_Controller;
use App\Http\Controllers\API\WpOrg\Plugins\PluginInformation_1_2_Controller;
use App\Http\Controllers\API\WpOrg\Plugins\PluginUpdateCheck_1_1_Controller;
use App\Http\Controllers\API\WpOrg\SecretKey\SecretKeyController;
use App\Http\Controllers\API\WpOrg\Themes\ThemeController;
use App\Http\Controllers\API\WpOrg\Themes\ThemeUpdatesController;
use App\Http\Controllers\PassThroughController;
use App\Http\Middleware\MetricsMiddleware;
use App\Http\Middleware\NormalizeWpOrgRequest;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

// Note: api routes are not prefixed, i.e. all routes in here are from the root like web routes

Route::prefix('/')
    ->middleware([
        'auth.optional:sanctum',
        NormalizeWpOrgRequest::class,
        MetricsMiddleware::class,
        'cache.headers:public;s_maxage=300,etag', // for the CDN's benefit: the WP user agent does not cache at all.
    ])
    ->group(function (Router $router) {
        // @formatter:off
        $router
            ->get('/metrics', MetricsController::class)
            ->name('api.metrics');

        //// FAIR metadata
        $router->get('/packages/{did}', [PackageInformationController::class, 'fairMetadata'])
            ->name('package.fairMetadata');

        $router->get('/packages/{type}/{slug}/did.json', [PackageInformationController::class, 'didDocument'])
            ->where('type', 'wp-plugin|wp-theme|wp-core')
            ->name('package.didDocument');

        Route::get('/plugins/search', [ElasticSearchController::class, 'searchPlugins']);

        //// Legacy API: https://codex.wordpress.org/WordPress.org_API
        $router
            ->any('/core/browse-happy/{version}', BrowseHappyController::class)
            ->where(['version' => '1.1'])
            ->name('api.wp.core.browse-happy');
        $router
            ->any('/core/serve-happy/{version}', ServeHappyController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.core.serve-happy');
        $router
            ->match(['get', 'post'], '/core/    stable-check/{version}', StableCheckController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.core.stable-check');
        $router
            ->get('/core/importers/{version}', ImportersController::class)
            ->where(['version' => '1.[01]'])
            ->name('api.wp.core.importers');

        $router
            ->get('/export/{type}', ExportController::class)
            ->whereIn('type', ['plugins', 'themes', 'closed_plugins'])
            ->name('api.wp.export');

        $router
            ->get('/plugins/info/1.0/{slug}.json', PluginInformation_1_0_Controller::class)
            ->name('api.wp.plugins.info.1_0');
        $router
            ->get('/plugins/info/1.2', PluginInformation_1_2_Controller::class)
            ->name('api.wp.plugins.info.1_2');
        $router
            ->post('/plugins/update-check/1.1', PluginUpdateCheck_1_1_Controller::class)
            ->name('api.wp.plugins.update-check');

        $router
            ->get('/secret-key/{version}', [SecretKeyController::class, 'index'])
            ->where(['version' => '1.[01]'])
            ->name('api.wp.secret-key.index');
        $router
            ->get('/secret-key/{version}/salt', [SecretKeyController::class, 'salt'])
            ->where(['version' => '1.1'])
            ->name('api.wp.secret-key.salt');

        $router
            ->get('/themes/info/{version}', [ThemeController::class, 'info'])
            ->where(['version' => '1.[012]'])
            ->name('api.wp.themes.info');
        $router
            ->match(['get', 'post'], '/themes/update-check/{version}', ThemeUpdatesController::class)
            ->where(['version' => '1.[01]'])
            ->name('api.wp.themes.update-check');

        /// Pass-through routes still going to .org

        $router
            ->any('/core/checksums/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.core.checksums');
        $router
            ->any('/core/credits/{version}', PassThroughController::class)
            ->where(['version' => '1.[01]'])
            ->name('api.wp.core.credits');
        $router
            ->any('/core/handbook/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.core.handbook');
        $router
            ->any('/core/version-check/{version}', PassThroughController::class)
            ->where(['version' => '1.[67]'])
            ->name('api.wp.core.version-check');

        $router
            ->any('/events/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.events');

        $router
            ->any('/patterns/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.patterns');

        // /plugins endpoints are implemented for version 1.2, older versions are still pass-through
        $router
            ->any('/plugins/info/{version}', PassThroughController::class)
            ->where(['version' => '1.[01]'])
            ->name('api.wp.plugins.info.legacy');
        $router
            ->any('/plugins/update-check/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.plugins.update-check.legacy');

        $router
            ->any('/stats/locale/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.stats.locale');
        $router
            ->any('/stats/mysql/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.stats.mysql');
        $router
            ->any('/stats/php/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.stats.php');
        $router
            ->any('/stats/plugin/{version}/downloads.php', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.stats.plugin.downloads');
        $router
            ->any('/stats/plugin/{version}/{slug}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.stats.plugin');
        $router
            ->any('/stats/wordpress/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.stats.wordpress');

        $router
            ->any('/translations/core/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.translations.core');
        $router
            ->any('/translations/plugins/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.translations.plugins');
        $router
            ->any('/translations/themes/{version}', PassThroughController::class)
            ->where(['version' => '1.0'])
            ->name('api.wp.translations.themes');

        // @formatter:on
    });

// Route::any('{path}', CatchAllController::class)->where('path', '.*');

require __DIR__ . '/inc/admin-api.php';
require __DIR__ . '/inc/download.php';
