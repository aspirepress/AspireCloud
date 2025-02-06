<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Str;

function makeApiRequest(string $method, string $uri, array $data = [], array $headers = [])
{
    $testCase = test();

    $user = User::factory()->create();
    $testCase = $testCase->actingAs($user);

    if (Str::lower($method) === 'post') {
        return $testCase->post($uri, $data, $headers);
    }

    // XXX shouldn't we be setting headers unconditionally?
    if (!empty($headers)) {
        return $testCase->withHeaders($headers)->get($uri);
    }

    return $testCase->{$method}($uri);
}
