<?php

declare(strict_types=1);

namespace App\Auth;

enum Permission: string
{
    case SearchAllResources = "any.search";
    case CreateAnyResource = "any.create";
    case ReadAnyResource = "any.read";
    case UpdateAnyResource = "any.update";
    case DeleteAnyResource = "any.delete";

    case SearchPlugins = "plugin.search";
    case CreatePlugin = "plugin.create";
    case ReadAnyPlugin = "plugin.read";
    case UpdateAnyPlugin = "plugin.update";
    case DeleteAnyPlugin = "plugin.delete";

    case SearchThemes = "theme.search";
    case CreateTheme = "theme.create";
    case ReadAnyTheme = "theme.read";
    case UpdateAnyTheme = "theme.update";
    case DeleteAnyTheme = "theme.delete";
}
