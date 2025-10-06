<?php

use App\Utils\Config;

return [
    'repos' => Config::stringList(env('FAIR_REPOS', '[]')),
    'paths' => [
        'packages' => '/wp-json/minifair/v1/packages/',
    ],
    'domains' => [
        'webdid' => env('FAIR_WEBDID_DOMAIN', null),
    ]
];
