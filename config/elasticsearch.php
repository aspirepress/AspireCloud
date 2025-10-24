<?php

return [
    'enabled' => env('ELASTICSEARCH_ENABLED', false),
    'host' => env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),
];
