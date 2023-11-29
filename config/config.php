<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'endpoint' => env('PENNYLANE_API_ENDPOINT', 'https://app.pennylane.tech/api/external/'),
    'v1_key' => env('PENNYLANE_API_KEY'),
    'v2_key' => env('PENNYLANE_API_V2_KEY')
];
