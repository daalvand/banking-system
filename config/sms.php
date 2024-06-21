<?php

return [
    'sender_number'  => env('SMS_SENDER_NUMBER'),
    'default_driver' => env('SMS_SENDER_DRIVER', 'kavenegar'),  // available drivers are [kavenegar, ghasedaksms]
    'services'       => [
        'kavenegar' => [
            'api_key'  => env('KAVENEGAR_API_KEY', 12345),
            'base_url' => 'https://api.kavenegar.com/v1',
            'timeout'  => 20,
        ]
    ]
];
