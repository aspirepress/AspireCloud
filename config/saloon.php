<?php

declare(strict_types=1);

use Saloon\Http\Senders\GuzzleSender;

return [
    'default_sender' => GuzzleSender::class,
    'integrations_path' => base_path('App/Http/Integrations'),
];
