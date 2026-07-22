<?php

declare(strict_types=1);

namespace App\zoospercore\tests\Unit\Documentation;

test('roadmap status keeps deferred near term items visible', function () {
    $root = dirname(__DIR__, 5);
    $status = (string) file_get_contents($root . '/docs/roadmap/roadmap-status.md');

    expect($status)->toContain('## Deferred near-term');
    expect($status)->toContain('1.37n.5');
    expect($status)->toContain('Optional media-gd/media-imagick processor package planning');
    expect($status)->toContain('1.38');
    expect($status)->toContain('RoleAdminController Latte/template migration');
    expect($status)->toContain('1.39');
    expect($status)->toContain('DB-backed rate limiting behind RateLimiterInterface');
});

test('deferred near term roadmap has durable parking file', function () {
    $root = dirname(__DIR__, 5);
    $parking = (string) file_get_contents($root . '/docs/roadmap/deferred-near-term.md');

    expect($parking)->toContain('Deferred Near-term Roadmap Items');
    expect($parking)->toContain('Launch Readiness Arc');
    expect($parking)->toContain('1.37n.5');
    expect($parking)->toContain('1.38');
    expect($parking)->toContain('1.39');
});
