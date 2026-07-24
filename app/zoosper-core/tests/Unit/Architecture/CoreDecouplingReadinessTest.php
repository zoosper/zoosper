<?php

declare(strict_types=1);

it('keeps core decoupling audit and planning tools available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/audit-core-downstream-module-dependencies.php')->toBeFile();
    expect($root . '/tools/plan-core-decoupling-phase-144.php')->toBeFile();
});

it('documents the Phase 1.44 decoupling plan', function (): void {
    $root = dirname(__DIR__, 5);
    $doc = $root . '/docs/development/core-decoupling-phase-1.44.md';

    expect($doc)->toBeFile();
    $contents = (string) file_get_contents($doc);
    expect($contents)->toContain('Fallback route decoupling');
    expect($contents)->toContain('Site context decoupling');
});
