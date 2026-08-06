<?php

declare(strict_types=1);

/**
 * Phase 9F5 guard: the canonical generator must remain free of HTTP/session
 * state and must replace only a leading /admin route segment.
 */
it('keeps canonical admin URL expansion deterministic and request independent', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/app/zoosper-core/src/Url/AdminUrlGenerator.php');

    expect($source)->toContain('expandCanonicalPath')
        ->toContain("str_starts_with(\$path, '/admin/')")
        ->not->toContain('$_SERVER')
        ->not->toContain('$_SESSION')
        ->not->toContain('getenv(');
});
