<?php

declare(strict_types=1);

return [
    'headers' => [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Permissions-Policy' => 'camera=(), microphone=(), geolocation=()',
    ],

    /*
     * Content-Security-Policy.
     *
     * Enforced by default. Admin scripts are same-origin and unsafe-eval is not
     * allowed. The reviewed unsafe-inline style exception remains for Editor.js
     * runtime stylesheet/layout behavior and the public Admin authentication and CSRF error responses.
     * See docs/security/admin-csp-validation.md.
     *
     * Set enabled => false to send no CSP header at all.
     */
    'csp' => [
        'enabled' => filter_var(env('SECURITY_CSP_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'report_only' => filter_var(env('SECURITY_CSP_REPORT_ONLY', false), FILTER_VALIDATE_BOOLEAN),
        'report_uri' => env('SECURITY_CSP_REPORT_URI'),
        'policy' => implode('; ', [
            "default-src 'self'",
            "base-uri 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "img-src 'self' data:",
            "font-src 'self' data:",
            "style-src 'self' 'unsafe-inline'",
            "script-src 'self'",
            "connect-src 'self'",
            "form-action 'self'",
        ]),
    ],

    /*
     * HTTP Strict-Transport-Security.
     *
     * Emitted ONLY on HTTPS requests (browsers ignore HSTS over HTTP, and
     * sending it on local plain-HTTP dev is pointless). Enable preload only once
     * you are certain every subdomain is HTTPS-only and you intend to submit to
     * the HSTS preload list.
     */
    'hsts' => [
        'enabled' => true,
        'max_age' => 31536000,
        'include_subdomains' => true,
        'preload' => false,
    ],
];








