<?php

declare(strict_types=1);

// As far as I can tell, /core/browse-happy is a telemetry endpoint returning nothing that browsers hit directly.
// Implementing it here checks off an item on the compatibility list, but it'll likely never see use in the real world.

namespace App\Http\Controllers\API\WpOrg\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BrowseHappyController extends Controller
{
    public function __invoke(Request $request): string
    {
        return '';
    }
}
