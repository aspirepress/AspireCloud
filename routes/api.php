<?php

// Note: api routes are not prefixed, i.e. all routes in here are from the root like web routes

use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::prefix('/')
    ->group(function (Router $r) {
        Route::get('/hello', fn () => ['message' => 'hello world']);
    });
