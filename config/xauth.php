<?php

return [
    'graph' => [
        'secret' => env('GRAPH_SECRET', 'INVALID SECRET'),
        'key' => env('GRAPH_KEY', 'INVALID KEY'),
        'callback_url' => env('GRAPH_CALLBACK', env('APP_URL') . '/login/graph/callback'),
        'tenant_id' => env('GRAPH_TENANT_ID', '35578d76-2cd7-4b88-8150-1ca2fddb6de5'),
        'include_tenant_info' => env('GRAPH_INCLUDE_TENANT_INFO', true),
    ],
    'uri' => [
        'login-success' => env('XAUTH_LOGIN_SUCCESS', '/'),
        'login-failed' => env('XAUTH_LOGIN_FAILED', '/'),
    ],
    'options' => [
        'get_avatars' => env('XAUTH_GET_AVATARS', true),
        'storage' => [
            'base_path' => env('XAUTH_STORAGE_PATH', 'public'),
            'disk' => env('XAUTH_LOGIN_STORAGE_DISK', ''),
        ],
        'chooseMethodView' => env('XAUTH_CHOOSE_METHOD_VIEW', ''),
    ],
];
