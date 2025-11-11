<?php
declare(strict_types=1);

use App\Http\Controllers\API\WpOrg\Downloads\DownloadCoreController;
use App\Http\Controllers\API\WpOrg\Downloads\DownloadPluginAssetController;
use App\Http\Controllers\API\WpOrg\Downloads\DownloadPluginController;
use App\Http\Controllers\API\WpOrg\Downloads\DownloadPluginIconController;
use App\Http\Controllers\API\WpOrg\Downloads\DownloadThemeController;
use App\Http\Controllers\API\WpOrg\Downloads\DownloadThemeScreenshotController;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

$cache_seconds = config('app.aspirecloud.download.cache_seconds');
$middleware = [
    "cache.headers:public;max_age=$cache_seconds", // we're streaming responses, so no etags
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
            ->where(['file' => '.+\.zip'])
            ->name('download.plugin');

        $router
            ->get('/download/theme/{file}', DownloadThemeController::class)
            ->where(['file' => '.+\.zip'])
            ->name('download.theme');

        $router
            ->get('/download/assets/plugin/{slug}/{revision}/{file}', DownloadPluginAssetController::class)
            ->where(['file' => '.+'])
            ->name('download.plugin.asset');

        $router
            ->get('/download/gp-icon/plugin/{slug}/{revision}/{file}', DownloadPluginIconController::class)
            ->where(['file' => '.+'])
            ->name('download.plugin.gp-icon');

        $router
            ->get('/download/assets/theme/{slug}/{revision}/{file}', DownloadThemeScreenshotController::class)
            ->where(['file' => '.+'])
            ->name('download.theme.asset');
    });
