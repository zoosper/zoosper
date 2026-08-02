<?php

declare(strict_types=1);

it('ships the API Grid hardening source exporter as a durable executable tool', function (): void {
    $root = dirname(__DIR__, 5);
    $tool = $root . '/tools/export-api-grid-hardening-source.sh';

    expect(is_file($tool))->toBeTrue();
    expect(is_executable($tool))->toBeTrue();
    $source = file_get_contents($tool);
    expect($source !== false)->toBeTrue();
    expect(str_contains($source, 'packages/zoosper-api-grid'))->toBeTrue();
    expect(str_contains($source, 'packages/zoosper-store-orders'))->toBeTrue();
    expect(str_contains($source, 'RELIABILITY REFERENCE SEARCH'))->toBeTrue();
});
