<?php

declare(strict_types=1);

namespace App\Contracts\Repo;

use App\Models\WpOrg\Plugin;

/**
 * @template-implements Repo<Plugin>
 */
interface PluginRepo extends Repo {}
