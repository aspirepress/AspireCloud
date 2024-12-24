<?php

declare(strict_types=1);

namespace App\Auth;

enum Permission: string
{
    case SearchAllResources = "any.search";
    case CreateResource = "any.create";
    case ReadAnyResource = "any.read";
    case UpdateAnyResource = "any.update";
    case DeleteAnyResource = "any.delete";

    case SearchPlugins = "plugin.search";
    case CreatePlugin = "plugin.create";
    case ReadPlugin = "plugin.read";
    case UpdatePlugin = "plugin.update";
    case DeletePlugin = "plugin.delete";

    case SearchThemes = "theme.search";
    case CreateTheme = "theme.create";
    case ReadTheme = "theme.read";
    case UpdateTheme = "theme.update";
    case DeleteTheme = "theme.delete";
}
