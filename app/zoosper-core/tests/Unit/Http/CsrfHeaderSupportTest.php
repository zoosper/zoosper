<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Http;

test('request exposes case-insensitive header lookup for csrf middleware', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-core/src/Http/Request.php');

    expect($source)->toContain('function header(string $name');
    expect($source)->toContain('strtolower($name)');
});

test('csrf middleware accepts x csrf token header for async editor uploads', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-auth/src/Http/CsrfMiddleware.php');

    expect($source)->toContain("header('x-csrf-token'");
    expect($source)->toContain('_csrf_token');
});
