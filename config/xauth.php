<?php

return [
    "graph" => [
        "secret" => env('GRAPH_SECRET', 'INVALID SECRET'),
        "key"    => env('GRAPH_KEY', 'INVALID KEY'),
        'callback_url' => env('GRAPH_CALLBACK', env('APP_URL') . '/login/graph/callback')
    ],
    "uri" => [
        "login-success" => env('XAUTH_LOGIN_SUCCESS', '/'),
        "login-failed" => env('XAUTH_LOGIN_FAILED', '/')
    ],
    "options" => [
        "get_avatars" => env('XAUTH_GET_AVATARS', true),
        "storage" => [
            "base_path" => env('XAUTH_STORAGE_PATH', 'public'),
            "disk" => env('XAUTH_LOGIN_STORAGE_DISK', '')
        ]
    ]
];
