<?php

declare(strict_types=1);

namespace App\Contracts\Repo;

use App\Models\WpOrg\Theme;

/**
 * @template-implements Repo<Theme>
 */
interface ThemeRepo extends Repo {}
