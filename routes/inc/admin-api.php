<?php

declare(strict_types=1);

use App\Actions\Admin\API\V1\BulkImport;
use App\Auth\Permission;
use App\Http\Middleware\RequireJson;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::prefix('admin/api/v1')
    ->middleware([
        'auth:sanctum',
        'permission:' . Permission::UseAdminSite->value,
        RequireJson::class,
    ])
    ->group(function (Router $router) {
        $router->post('/import', BulkImport::class)->can(Permission::BulkImport);
    });
