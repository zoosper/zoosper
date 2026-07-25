<?php

declare(strict_types=1);

it('loads durable tool allowlist from the durable tool registry', function (): void {
    $root = dirname(__DIR__, 5);
    $audit = $root . '/tools/audit-architecture-foundation-gates.php';

    expect(is_file($audit))->toBeTrue();

    $source = file_get_contents($audit) ?: '';

    expect($source)->toContain('config/durable_tools.php');
    expect($source)->toContain('loadDurableToolAllowlist');
    expect($source)->not->toContain('tools/apply-role-admin-latte-cutover.php\',');
    expect($source)->not->toContain('tools/apply-role-admin-markup-view-cutover.php\',');
});

it('keeps registered durable apply/cutover tools out of temporary artefact detection', function (): void {
    $root = dirname(__DIR__, 5);
    $registry = require $root . '/config/durable_tools.php';

    expect($registry)->toHaveKey('tools/apply-role-admin-latte-cutover.php');
    expect($registry)->toHaveKey('tools/apply-role-admin-markup-view-cutover.php');

    foreach (array_keys($registry) as $tool) {
        expect(is_file($root . '/' . $tool))->toBeTrue();
    }
});
