<?php

return [
    'driver' => env('WHATSAPP_DRIVER', 'log'),

    'api_key' => env('WHATSAPP_API_KEY'),

    'api_url' => env('WHATSAPP_API_URL', 'https://api.fonnte.com/send'),
];
