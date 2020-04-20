<?php

declare(strict_types=1);

return [
    'auth'        => [
        'driver_name' => 'guardian',
        'authority'   => 'default',
        'strategy'    => 'eloquent',
        'eloquent'    => [
            'model'      => '\\App\\User',
            'identifier' => null, // If set to null, use the default model key name
        ],
        'database'    => [
            'connection' => null, // If set to null, use the default database connection
            'table'      => 'users',
            'identifier' => 'id',
        ],
    ],
    'authorities' => [
        'default' => [
            'key'    => env('JWT_KEY', 'default'),
            'claims' => env('JWT_CLAIMS', 'default'),
        ],
    ],
    'keys'        => [
        'default' => [
            'algorithm' => env('JWT_KEY_ALGORITHM', 'HS512'),
            'size'      => env('JWT_KEY_SIZE', 1024),
            'path'      => env('JWT_KEY_PATH', storage_path('jwt_auth.json')),
        ],
    ],
    'claims'      => [
        'default' => [
            // "iss" (Issuer), see https://tools.ietf.org/html/rfc7519#section-4.1.1
            'iss' => env('JWT_CLAIMS_ISS', 'Your Issuer'),
            // "aud" (Audience), see https://tools.ietf.org/html/rfc7519#section-4.1.3
            'aud' => env('JWT_CLAIMS_AUD', 'Your Audience'),
            // "exp" (Expiration Time), see https://tools.ietf.org/html/rfc7519#section-4.1.4
            'exp' => env('JWT_CLAIMS_EXP', '+3 months'),
            // "nbf" (Not Before), see https://tools.ietf.org/html/rfc7519#section-4.1.5
            'nbf' => env('JWT_CLAIMS_NBF', 'now'),
            // "iat" (Issued At), see https://tools.ietf.org/html/rfc7519#section-4.1.6
            'iat' => env('JWT_CLAIMS_IAT', 'now'),
            // "jti" (JWT ID), see https://tools.ietf.org/html/rfc7519#section-4.1.7
            'jid' => env('JWT_CLAIMS_JID', 'uuid'),
        ],
    ],
];
