<?php

declare(strict_types=1);

namespace App\Auth;

enum Permission: string
{
    case SearchAllResources = "any.search";
    case CreateAnyResource = "any.create";
    case ReadAnyResource = "any.read";
    // case UpdateAnyResource = "any.update";  // resources are immutable
    case DeleteAnyResource = "any.delete";
}
