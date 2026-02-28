<?php

return [
    'name' => 'Whatsapp',
    'api_base_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com'),
    'api_version' => env('WHATSAPP_API_VERSION', 'v18.0'),
    'short_codes' => ['{name}', '{phone}'],
];
