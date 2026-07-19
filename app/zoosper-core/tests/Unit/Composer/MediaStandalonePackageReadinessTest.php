<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Composer;

test('media package exposes standalone repository metadata', function () {
    $root = dirname(__DIR__, 5);
    $composer = json_decode((string) file_get_contents($root . '/packages/zoosper-media/composer.json'), true);

    expect($composer['name'])->toBe('zoosper/media');
    expect($composer['type'])->toBe('zoosper-module');
    expect($composer['require'])->toHaveKeys(['php', 'ext-pdo', 'zoosper/core', 'zoosper/admin', 'zoosper/auth']);
    expect($composer['autoload']['psr-4']['Zoosper\\Media\\'])->toBe('src/');
    expect($composer['autoload-dev']['psr-4']['Zoosper\\Media\\Tests\\'])->toBe('tests/');
    expect($composer['extra']['zoosper']['module'])->toBe('module.php');
});

test('media package includes standalone testing and repository support files', function () {
    $root = dirname(__DIR__, 5);
    $package = $root . '/packages/zoosper-media';

    expect(is_file($package . '/phpunit.xml.dist'))->toBeTrue();
    expect(is_file($package . '/README.md'))->toBeTrue();
    expect(is_file($package . '/.gitignore'))->toBeTrue();
    expect(is_file($package . '/.github/workflows/tests.yml'))->toBeTrue();
});

test('media standalone package audit tool checks required package boundaries', function () {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents($root . '/tools/audit-media-standalone-package.php');

    expect($source)->toContain('zoosper/media');
    expect($source)->toContain('zoosper-module');
    expect($source)->toContain('phpunit.xml.dist');
    expect($source)->toContain('.github/workflows/tests.yml');
});
