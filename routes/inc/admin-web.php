<?php

declare(strict_types=1);

use App\Auth\Permission;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')
    ->middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
        'permission:' . Permission::UseAdminSite->value,
    ])
    ->group(function (Router $router) {
        // ...
    });
