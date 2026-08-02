<?php

declare(strict_types=1);

it('guards API Grid source exports against silent truncation', function (): void {
    $root = dirname(__DIR__, 5);
    $tool = $root . '/tools/export-api-grid-hardening-source.sh';
    expect(is_file($tool))->toBeTrue();
    $source = file_get_contents($tool);
    expect($source !== false)->toBeTrue();
    expect(str_contains($source, 'EXPORTED_COUNT'))->toBeTrue();
    expect(str_contains($source, 'implausibly small'))->toBeTrue();
    expect(str_contains($source, "read -r -d '' file"))->toBeTrue();
});
