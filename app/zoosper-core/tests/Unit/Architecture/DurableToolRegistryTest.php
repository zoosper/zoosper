<?php

declare(strict_types=1);

it('registers durable tools that should survive cleanup phases', function (): void {
    $root = dirname(__DIR__, 5);
    $registryFile = $root . '/config/durable-tools.php';

    expect(is_file($registryFile))->toBeTrue();

    $registry = require $registryFile;

    expect($registry)->toHaveKey('tools/apply-role-admin-latte-cutover.php');
    expect($registry)->toHaveKey('tools/apply-role-admin-markup-view-cutover.php');

    foreach ($registry as $tool => $metadata) {
        expect($tool)->toStartWith('tools/');
        expect(is_file($root . '/' . $tool))->toBeTrue();
        expect($metadata['reason'] ?? '')->not->toBe('');
    }
});

it('provides a durable tool registry audit command', function (): void {
    $root = dirname(__DIR__, 5);
    $tool = $root . '/tools/audit-durable-tool-registry.php';

    expect(is_file($tool))->toBeTrue();

    $source = file_get_contents($tool) ?: '';
    expect($source)->toContain('Durable Tool Registry Audit');
    expect($source)->toContain('config/durable-tools.php');
});
