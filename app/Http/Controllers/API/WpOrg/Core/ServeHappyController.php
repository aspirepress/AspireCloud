<?php

declare(strict_types=1);

namespace App\Http\Controllers\API\WpOrg\Core;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServeHappyController extends Controller
{
    // "Recommended" according to an ancient wp.org api anyway.  Not useful for anything but mimicking said api.
    private const string RECOMMENDED_PHP = '7.4';
    private const string MINIMUM_PHP = '7.2.24';
    private const string SUPPORTED_PHP = '7.4';
    private const string SECURE_PHP = '7.4';
    private const string ACCEPTABLE_PHP = '7.4';

    public function __invoke(Request $request): JsonResponse
    {
        $params = $request->validate(['php_version' => 'required|string']);
        $php_version = $params['php_version'];

        // wp.org also supports jsonp.  we do not and never will.
        return new JsonResponse([
            'recommended_version' => self::RECOMMENDED_PHP,
            'minimum_version' => self::MINIMUM_PHP,
            'is_supported' => version_compare($php_version, self::SUPPORTED_PHP, '>='),
            'is_secure' => version_compare($php_version, self::SECURE_PHP, '>='),
            'is_acceptable' => version_compare($php_version, self::ACCEPTABLE_PHP, '>='),
        ]);
    }
}
