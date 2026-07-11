<?php

declare(strict_types=1);

return [
    /*
     * Public root relative to the project base path. Runtime/private files must
     * not be created here. Public should contain only the front controller and
     * intentionally published static/media assets.
     */
    'public_path' => 'public',

    /*
     * Directory roots that should never be publicly browsable or used as a
     * runtime storage target. Examples: public/var and public/storage.
     */
    'blocked_roots' => [
        '/var/',
        '/storage/',
        '/vendor/',
        '/app/',
        '/config/',
        '/modules/',
        '/tools/',
        '/logs/',
        '/log/',
        '/cache/',
        '/private/',
        '/tmp/',
    ],

    /*
     * Executable/server-side file extensions that must never be served from
     * public/, except for the front controller public/index.php.
     */
    'blocked_extensions' => [
        'php', 'phtml', 'phar', 'php3', 'php4', 'php5', 'php7', 'php8',
        'cgi', 'pl', 'py', 'rb', 'sh', 'bash', 'zsh', 'fish', 'exe', 'dll', 'so',
    ],

    /*
     * Extensions expected under public/static and public/assets. Media library
     * validation should later get a dedicated stricter policy.
     */
    'allowed_static_extensions' => [
        'css', 'js', 'mjs', 'json', 'map', 'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp',
        'avif', 'ico', 'woff', 'woff2', 'ttf', 'eot', 'txt', 'xml', 'webmanifest',
    ],

    /*
     * Paths that are intentionally public. Anything else is not automatically
     * suspicious, but the audit tool will highlight blocked dirs/extensions.
     */
    'expected_public_roots' => [
        '/static/',
        '/assets/',
        '/media/',
    ],

    /*
     * The only PHP file allowed to be web-accessible. Nginx should still route
     * requests through PHP-FPM explicitly rather than download this file.
     */
    'allowed_php_files' => [
        '/index.php',
    ],

    /*
     * Where the quarantine tool moves suspicious files. This must remain outside
     * public/ to avoid exposing quarantined files in the browser.
     */
    'quarantine_path' => 'var/quarantine/public-webroot',
];
