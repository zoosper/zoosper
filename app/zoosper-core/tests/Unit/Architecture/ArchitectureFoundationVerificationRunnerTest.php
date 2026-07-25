<?php

declare(strict_types=1);

it('keeps the architecture foundation verification runner focused on permanent guards', function (): void {
    $root = dirname(__DIR__, 5);
    $runner = $root . '/tools/verify-architecture-foundation.php';

    expect(is_file($runner))->toBeTrue();

    $source = file_get_contents($runner) ?: '';

    expect($source)->toContain('audit-core-feature-coupling.php');
    expect($source)->toContain('audit-core-feature-decoupling-closure.php');
    expect($source)->toContain('audit-site-lookup-boundary-regression.php');
    expect($source)->toContain('audit-site-lookup-service-binding.php');
    expect($source)->toContain('audit-site-lookup-service-binding-regression.php');
    expect($source)->toContain('audit-architecture-foundation-gates.php');
});

it('keeps the architecture foundation verification runner read-only and audit-scoped', function (): void {
    $root = dirname(__DIR__, 5);
    $source = file_get_contents($root . '/tools/verify-architecture-foundation.php') ?: '';

    expect($source)->not->toContain('--apply');
    expect($source)->not->toContain('composer dump-autoload');
    expect($source)->not->toContain('vendor/bin/pest');
    expect($source)->toContain('Run Composer and Pest separately as release gates.');
});
