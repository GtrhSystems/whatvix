<?php

return [

    /**
     * The root template that is loaded on the first page visit.
     * 
     * points to resources/views/layouts/*
     * string(static), array(dynamic)
     */

    'root_view_prefix' => 'layouts',

    'root_views' => [
        'admin' => 'admin',
        'user' => 'user',
        'login' => 'auth',
        'register' => 'auth',
        'forgot-password' => 'auth',
        'reset-password' => 'auth',
        'verify-email' => 'auth',
        'confirm-password' => 'auth',
    ],

    'default_view' => 'default',

];
