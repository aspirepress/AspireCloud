<?php

use App\Http\Controllers\API\WpOrg\Downloads\DownloadCoreController;
use App\Http\Controllers\API\WpOrg\Downloads\DownloadPluginAssetController;
use App\Http\Controllers\API\WpOrg\Downloads\DownloadPluginController;
use App\Http\Controllers\API\WpOrg\Downloads\DownloadThemeScreenshotController;
use App\Http\Controllers\API\WpOrg\Downloads\DownloadThemeController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

$auth_middleware = config('app.aspirecloud.api_authentication_enable') ? ['auth:sanctum'] : [];
$middleware = [
    'cache.headers:public;max_age=60', // cache 302 redirects for 1 minute while we fetch it
    ...$auth_middleware,
];

Route::prefix('/')
    ->middleware($middleware)
    ->group(function (Router $router) {
        $router
            ->get('/download/wordpress-{version}.{extension}', DownloadCoreController::class)
            ->where(['version' => '[\d.]+', 'extension' => 'zip|tar\.gz'])
            ->name('download.core');

        $router
            ->get('/download/plugin/{file}', DownloadPluginController::class)
            ->where('file', '.+\.zip')
            ->name('download.plugin');

        $router
            ->get('/download/theme/{file}', DownloadThemeController::class)
            ->where('file', '.+\.zip')
            ->name('download.theme');

        $router
            ->get('/download/{slug}/assets/{file}', DownloadPluginAssetController::class)
            ->where(['slug' => '[a-zA-Z0-9-]+', 'file' => '.+'])
            ->name('download.plugin.asset');

        // $router->get('/download/theme/{slug}/screenshots/{file}', DownloadThemeScreenshotController::class)
        //     ->where(['slug' => '[a-zA-Z0-9-]+', 'file' => '.+'])
        //     ->name('download.theme.screenshot');

        // alternative asset url syntax, will replace the above two.  use 'head' for the empty revision
        // $router->get('/download/assets/plugin/{slug}/{revision}/{file}', DownloadPluginAssetController::class)
        //     ->where(['slug' => '[a-zA-Z0-9-]+', 'file' => '.+'])
        //     ->name('download.plugin.asset');
        //
        $router
            ->get('/download/assets/theme/{slug}/{revision}/{file}', DownloadThemeScreenshotController::class)
            ->where(['slug' => '[a-zA-Z0-9-]+', 'file' => '.+'])
            ->name('download.theme.asset');
    });
