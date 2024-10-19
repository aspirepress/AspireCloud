<?php

use App\Http\Controllers\CatchAllController;
use App\Http\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

$passThroughEnabled = (bool) env('AC_PASS_THROUGH', false);
if (!$passThroughEnabled) {
    Route::group(['prefix' => 'themes/info'], function () {
        Route::get('1.0', [ThemeController::class, 'infoV1']); // PHP serialized response
        Route::get('1.1', [ThemeController::class, 'infoV1_1']); // JSON response
        Route::get('1.2', [ThemeController::class, 'infoV1_2']); // JSON response
    });
}

Route::any('{path}', [CatchAllController::class, 'handle'])->where('path', '.*');
