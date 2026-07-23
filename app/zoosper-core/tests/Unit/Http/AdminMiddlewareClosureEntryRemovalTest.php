<?php

declare(strict_types=1);

it('keeps the Closure admin middleware removal tool available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/remove-closure-admin-middleware-entries.php')->toBeFile();
});

it('has no Closure entries in admin middleware config files after hotfix is applied', function (): void {
    $root = dirname(__DIR__, 5);
    $files = array_merge(
        glob($root . '/app/*/config/admin_middleware.php') ?: [],
        glob($root . '/packages/*/config/admin_middleware.php') ?: [],
        glob($root . '/packages/*/*/config/admin_middleware.php') ?: [],
    );

    foreach ($files as $file) {
        $config = require $file;
        expect($config)->toBeArray();

        $entries = $config;
        if (isset($entries['admin']) && is_array($entries['admin'])) {
            $entries = $entries['admin'];
        } elseif (isset($entries['middleware']) && is_array($entries['middleware'])) {
            $entries = $entries['middleware'];
        }

        foreach ($entries as $entry) {
            expect($entry)->not->toBeInstanceOf(Closure::class);
            expect($entry)->toBeString();
            expect($entry)->not->toBe('');
        }
    }
});
