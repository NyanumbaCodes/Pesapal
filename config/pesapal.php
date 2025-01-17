<?php

return [
    'consumer_key' => env('PESAPAL_CONSUMER_KEY'),
    'currency' => env('PESAPAL_CURRENCY'),
    'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),
    'callback_url' => env('PESAPAL_CALLBACK_URL'),
    'env' => env('PESAPAL_ENV', 'sandbox'),
];
