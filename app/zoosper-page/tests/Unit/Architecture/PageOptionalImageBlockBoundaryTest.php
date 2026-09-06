<?php

declare(strict_types=1);

it('keeps Page independent from the optional Media image implementation', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode((string) file_get_contents($root . '/app/zoosper-page/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $renderer = (string) file_get_contents($root . '/app/zoosper-page/src/Content/BlockJsonToHtmlRenderer.php');
    $services = (string) file_get_contents($root . '/app/zoosper-page/config/services.php');

    expect($composer['require'])->not->toHaveKey('zoosper/media')
        ->and($renderer)->toContain('EditorImageBlockSanitizerInterface')->not->toContain('Zoosper\\Media\\')
        ->and($services)->toContain('$services->has(EditorImageBlockSanitizerInterface::class)')
        ->not->toContain('Zoosper\\Media\\');
});
