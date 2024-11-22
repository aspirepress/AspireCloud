<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Str;

/**
 * Helper function to make an authenticated request or not
 * based on the configuration.
 * @param mixed $method
 * @param mixed $uri
 * @param mixed $data
 * @param mixed $headers
 */
function makeApiRequest($method, $uri, $data = [], $headers = [])
{
    $isAuthEnabled = config('app.aspire_press.api_authentication_enable');
    $testCase      = test();

    if ($isAuthEnabled) {
        $user     = User::factory()->create();
        $testCase = $testCase->actingAs($user);
    }

    if (Str::lower($method) === 'post') {
        return $testCase->post($uri, $data, $headers);
    }

    // sent the header on get request too
    if (!empty($headers)) {
        return $testCase->withHeaders($headers)->get($uri);
    }

    return $testCase->{$method}($uri);
}
