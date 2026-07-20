<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Tools;

test('composer php85 wrapper forces composer through php 8.5 binary', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/composer-php85.sh');

    expect($source)->toContain('PHP_BIN="${PHP_BIN:-php8.5}"');
    expect($source)->toContain('COMPOSER_BIN="${COMPOSER_BIN:-$(command -v composer)}"');
    expect($source)->toContain('exec "$PHP_BIN" "$COMPOSER_BIN" "$@"');
});

test('php85 toolchain operations document explains why PHP env var is not enough', function () {
    $root = dirname(__DIR__, 5);
    $doc = (string) file_get_contents($root . '/docs/operations/php85-composer-toolchain.md');

    expect($doc)->toContain('php8.5 $(which composer) dump-autoload');
    expect($doc)->toContain('PHP=php8.5 composer dump-autoload');
    expect($doc)->toContain('@php');
});
