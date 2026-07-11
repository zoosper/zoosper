<?php

declare(strict_types=1);

$env = static fn (string $key, mixed $default = null): mixed => $_ENV[$key] ?? getenv($key) ?: $default;

return [
    /*
     * Default sanitizer driver. "htmlpurifier" is recommended for WYSIWYG/rich
     * HTML content. "basic" exists only as a conservative fallback for local
     * development when the Composer dependency is not installed yet.
     */
    'driver' => (string) $env('HTML_SANITIZER_DRIVER', 'htmlpurifier'),

    /*
     * Cache directory used by HTML Purifier. The directory must be writable by
     * the PHP user. Do not store secrets in this directory.
     */
    'cache_path' => (string) $env('HTML_SANITIZER_CACHE_PATH', 'var/cache/htmlpurifier'),

    /*
     * A restrictive baseline suitable for CMS body content. This is not meant
     * for admin forms, OTP values, payment data, secrets, or token-bearing HTML.
     */
    'allowed_elements' => (string) $env(
        'HTML_SANITIZER_ALLOWED_ELEMENTS',
        'p,br,strong,b,em,i,u,ul,ol,li,a[href|title|target|rel],h2,h3,h4,h5,h6,blockquote,pre,code,img[src|alt|title|width|height],figure,figcaption,table,thead,tbody,tr,th,td'
    ),

    'allowed_schemes' => (string) $env('HTML_SANITIZER_ALLOWED_SCHEMES', 'http,https,mailto,tel'),
    'strip_empty' => filter_var($env('HTML_SANITIZER_STRIP_EMPTY', true), FILTER_VALIDATE_BOOLEAN),
];
