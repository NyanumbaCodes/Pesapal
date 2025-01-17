<?php

return [
    'consumer_key' => env('PESAPAL_CONSUMER_KEY'),
    'consumer_secret' => env('PESAPAL_CONSUMER_SECRET'),
    'currency' => env('PESAPAL_CURRENCY','KES'),
    'merchant' => env('PESAPAL_MERCHANT_NAME','PESAPAL'),
    'ipn_url' => env('PESAPAL_IPN_URL'),
    'callback_url' => env('PESAPAL_CALLBACK_URL'),
    'env' => env('PESAPAL_ENV', 'sandbox'),
];
