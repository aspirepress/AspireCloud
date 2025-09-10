<?php

return [
    'repos' => json_decode(env('FAIR_REPOS', '[]'), true), # JSON array of FAIR repo base URLs
    'paths' => [
        'packages' => '/wp-json/minifair/v1/packages/',
    ],
];
