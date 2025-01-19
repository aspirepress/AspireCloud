<?php

declare(strict_types=1);

namespace App\Contracts\Repo;

/**
 * Represents the general concept of a plugin/theme repository service, in the sense of "the wp.org theme repo"
 * or "the github plugin repo" rather than a lower-level Entity/Model Repository, thus the abbreviated "Repo" name.
 *
 * @template T
 */
interface Repo
{
    public function origin(): string;
}
