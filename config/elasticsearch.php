<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "algolia", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'elasticsearch'),

    /*
    |--------------------------------------------------------------------------
    | Default Search config
    |--------------------------------------------------------------------------
    */
    'elasticsearch' => [
        'index' => env('ELASTICSEARCH_INDEX', 'test'),
        'type' => env('ELASTICSEARCH_type', 'student'),
        'hosts' => [
            env('ELASTICSEARCH_HOST', 'http://localhost'),
        ],
    ],
];
