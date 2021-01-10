<?php

return [
    "graph" => [
        "secret" => env('GRAPH_SECRET', 'INVALID SECRET'),
        "key"    => env('GRAPH_KEY', 'INVALID KEY')
    ],
    "uri" => [
        "login-success" => env('XAUTH_LOGIN_SUCCESS', '/'),
        "login-failed" => env('XAUTH_LOGIN_FAILED', '/')
    ]
];
