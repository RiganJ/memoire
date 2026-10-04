<?php

return [

    'env' => env('DANA_ENV', 'sandbox'),

    'base_url' => env(
        'DANA_BASE_URL',
        'https://api.sandbox.dana.id'
    ),

    'merchant_id' => env('DANA_MERCHANT_ID'),

    'x_partner_id' => env('DANA_CLIENT_ID'),

    'client_secret' => env('DANA_CLIENT_SECRET'),

    'private_key_path' => base_path(
        env(
            'DANA_PRIVATE_KEY_PATH',
            'storage/app/keys/dana_private_key.pem'
        )
    ),

    'store_id' => env('DANA_STORE_ID'),

    'channel_id' => env('DANA_CHANNEL_ID'),

];
