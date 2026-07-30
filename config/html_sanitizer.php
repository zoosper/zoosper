<?php

declare(strict_types=1);

/**
 * BUG FIX (confirmed 2026-07-30, external reviewer pass): the previous
 * $env closure here was:
 *   static fn (string $key, mixed $default = null): mixed => $_ENV[$key] ?? getenv($key) ?: $default;
 * PHP's ?? binds tighter than ?:, so this actually evaluates as
 * ($_ENV[$key] ?? getenv($key)) ?: $default — meaning ANY explicitly-set
 * but falsy value (e.g. HTML_SANITIZER_STRIP_EMPTY=0, or an intentionally
 * empty string) silently collapses back to the default, as if it had never
 * been set at all. Concretely: someone explicitly setting
 * HTML_SANITIZER_STRIP_EMPTY=0 (wanting false) would have it silently
 * overridden back to the default (true) — the opposite of their stated
 * intent.
 *
 * Replaced with the same, already-correct $env implementation already used
 * in config/two_factor.php and config/rate_limit.php elsewhere in this
 * codebase — checks isset()/array_key_exists() and empty-string explicitly,
 * rather than relying on PHP's ??/?: precedence together.
 *
 * SECURITY FIX (confirmed 2026-07-30, same reviewer pass): added
 * 'allow_basic_driver', defaulting to false. See HtmlSanitizerFactory for
 * where this is enforced — selecting HTML_SANITIZER_DRIVER=basic without
 * also explicitly setting HTML_SANITIZER_ALLOW_BASIC_DRIVER=true now fails
 * loudly at the point the sanitizer is actually constructed, rather than
 * silently using a conservative, regex-based fallback (which its own
 * docblock admits is bypassable) for real, stored CMS content.
 */
$env = static function (string $key, mixed $default = null): mixed {
    if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
        return $_ENV[$key];
    }

    $value = getenv($key);
    return $value !== false && $value !== '' ? $value : $default;
};

return [
    /*
     * Default sanitizer driver. "htmlpurifier" is recommended for WYSIWYG/rich
     * HTML content. "basic" exists only as a conservative fallback for local
     * development when the Composer dependency is not installed yet.
     */
    'driver' => (string) $env('HTML_SANITIZER_DRIVER', 'htmlpurifier'),

    /*
     * Must be explicitly set to true, alongside driver=basic, to actually use
     * the basic (regex-based, conservative) fallback sanitizer. See
     * HtmlSanitizerFactory — this exists so an operator must make a
     * deliberate, separate choice to accept weaker sanitisation, rather than
     * it happening silently (e.g. if HTMLPurifier is ever missing from a
     * deploy and someone flips the driver "to make it work").
     */
    'allow_basic_driver' => filter_var($env('HTML_SANITIZER_ALLOW_BASIC_DRIVER', false), FILTER_VALIDATE_BOOLEAN),

    /*
     * Cache directory used by HTML Purifier. The directory must be writable by
     * the PHP user. Do not store secrets in this directory.
     */
    'cache_path' => (string) $env('HTML_SANITIZER_CACHE_PATH', 'var/cache/htmlpurifier'),

    /*
     * Restrictive baseline for CMS body content. Unsupported HTML Purifier tags
     * such as figure/figcaption are intentionally omitted until explicit custom
     * definitions are added. This config is not for OTPs, payment data, reset
     * tokens, SMTP passwords or other secrets.
     */
    'allowed_elements' => (string) $env(
        'HTML_SANITIZER_ALLOWED_ELEMENTS',
        'p,br,strong,b,em,i,u,ul,ol,li,a[href|title|target|rel],h2,h3,h4,h5,h6,blockquote,pre,code,img[src|alt|title|width|height],table,thead,tbody,tr,th,td'
    ),

    'allowed_schemes' => (string) $env('HTML_SANITIZER_ALLOWED_SCHEMES', 'http,https,mailto,tel'),
    'strip_empty' => filter_var($env('HTML_SANITIZER_STRIP_EMPTY', true), FILTER_VALIDATE_BOOLEAN),
];
