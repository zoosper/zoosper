<?php

declare(strict_types=1);

it('keeps Settings as the priority immediately after API Grid closure', function (): void {
    $root = dirname(__DIR__, 5);
    $roadmapPath = $root . '/ROADMAP.md';

    expect(is_file($roadmapPath))->toBeTrue();
    $roadmap = file_get_contents($roadmapPath);
    expect($roadmap !== false)->toBeTrue();
    expect(str_contains($roadmap, '## Priority after API Grid closure: modern Settings platform'))->toBeTrue();
    expect(str_contains($roadmap, 'Phase S0 — inventory and ownership audit'))->toBeTrue();
    expect(str_contains($roadmap, 'Settings reads use one resolver'))->toBeTrue();
});

it('ships the second-pilot boundary audit as a durable tool', function (): void {
    $root = dirname(__DIR__, 5);
    expect(is_file($root . '/tools/audit-api-grid-second-pilot-boundaries.php'))->toBeTrue();
});
