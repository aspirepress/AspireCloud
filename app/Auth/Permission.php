<?php

declare(strict_types=1);

namespace App\Auth;

enum Permission: string
{
    case UseAdminSite = "admin.use"; // only controls access to routes -- actions will do their own auth checks
    case BulkImport = "admin.bulk-import";
}
