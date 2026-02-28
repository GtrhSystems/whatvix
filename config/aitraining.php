<?php

return [
    /**
     * The `provider_config` is a configuration array. Each key in the array represents a
     * provider and its value is an array containing the configuration for that provider.
     */
    'provider_config' => [
        'gemini' => [
            'schema' => [
                'base_url' => 'https://generativelanguage.googleapis.com',
                'project_id' => '',
                'client_secret' => '',
                'client_id' => '',
                'redirect' => '',
                'gemini_api_key' => '',
            ],
            'info' => [
                'name' => 'Gemini',
                'icon' => 'logos:google-gemini',
                'o_auth' => 'https://gemini.com',
                'api_url' => 'https://api.gemini.com',
            ]
        ],
    ],

];
