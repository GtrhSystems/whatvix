<?php

return [
    'name' => 'OpenAi',
    'schema' => [
        'base_url' => 'https://api.openai.com/v1',
        'api_key' => '',
        'model' => null,
    ],
    'info' => [
        'name' => 'OpenAi',
        'icon' => 'logos:openai',
        'supported_models' => ['gpt-4o-mini-2024-07-18', 'gpt-4o-2024-08-06', 'gpt-4-0613'],
    ]
];
