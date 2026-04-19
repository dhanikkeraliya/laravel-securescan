<?php

return [

    'route_prefix' => '_securescan',

    'middleware' => [
        'web',
        // 'auth'  ← recommended later
    ],
    // Enable/Disable scanners
    'scanners' => [
        'sql_injection' => true,
        'xss' => true,
        'debug' => true,
        'secrets' => true,
        'file_upload' => true,
        'csrf' => true,
        'dangerous_functions' => true,
        'mass_assignment' => true,
        'open_redirect' => true,
        'random' => true,
        'authorization' => true,
        'rate_limit' => true,
        'input' => true,
        'env' => true,
        'logging' => true,
        'url' => true
    ],

    // Ignore severity levels
    'ignore_severity' => [
        // 'LOW',
    ],

    // Ignore specific files or patterns
    'ignore_paths' => [
        'storage/',
        'vendor/',
    ],
];