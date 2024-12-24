<?php

declare(strict_types=1);

namespace App\Auth;

enum Permission: string
{
    case SearchResources = "resource.search";
    case CreateResource = "resource.create";
    case ReadResource = "resource.read";
    // case UpdateResource = "resource.update";  // resources are immutable
    case DeleteResource = "resource.delete";
}
