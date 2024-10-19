<?php

use App\Http\Controllers\CatchAllController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::any('{path}', [CatchAllController::class, 'handle'])->where('path', '.*');
