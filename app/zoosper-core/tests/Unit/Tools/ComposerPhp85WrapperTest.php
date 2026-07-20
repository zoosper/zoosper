<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Tools;

test('php85 composer operations document gives the canonical composer command', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/operations/php85-composer-toolchain.md');

    expect($doc)->toContain('php8.5 $(which composer) dump-autoload');
    expect($doc)->toContain('php8.5 vendor/bin/pest');
    expect($doc)->toContain('PHP=php8.5 bin/verify');
});

test('php85 composer operations document explains why PHP env var is not enough', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/operations/php85-composer-toolchain.md');

    expect($doc)->toContain('PHP=php8.5 composer dump-autoload');
    expect($doc)->toContain('@php');
    expect($doc)->toContain('Composer itself must be launched by PHP 8.5');
});

test('php85 composer guidance no longer depends on a shell wrapper tool', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/operations/php85-composer-toolchain.md');

    expect($doc)->not->toContain('tools/composer-php85.sh');
});
