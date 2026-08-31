<?php

declare(strict_types=1);

it('declares packages referenced by Page runtime composition', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode((string) file_get_contents($root . '/app/zoosper-page/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $services = (string) file_get_contents($root . '/app/zoosper-page/config/services.php');

    expect($services)->toContain('Zoosper\Media\EditorJs\EditorJsImageBlockSanitizer')
        ->and($composer['require'])->toHaveKey('zoosper/media', 'dev-dev')
        ->toHaveKey('zoosper/core', 'dev-dev')
        ->toHaveKey('zoosper/site', 'dev-dev')
        ->toHaveKey('zoosper/theme', 'dev-dev');
});










