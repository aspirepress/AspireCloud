<?php
declare(strict_types=1);

return [
    'auto_index' => env('ELASTICSEARCH_AUTO_INDEX', false),
    'host' => env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'),
];
