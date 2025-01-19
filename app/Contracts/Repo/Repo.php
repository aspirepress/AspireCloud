<?php

declare(strict_types=1);

namespace App\Contracts\Repo;

use App\Models\WpOrg\Plugin;
use App\Models\WpOrg\Theme;

/**
 * Represents the general concept of a plugin/theme repository service, in the sense of "the wp.org theme repo"
 * or "the github plugin repo" rather than a lower-level Entity/Model Repository, thus the abbreviated "Repo" name.
 *
 * @template T of Plugin|Theme
 */
interface Repo
{
    public function origin(): string;
}
