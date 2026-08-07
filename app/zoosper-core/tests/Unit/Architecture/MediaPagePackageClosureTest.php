<?php

declare(strict_types=1);

it('locks completed Media decoupling and Page structured rendering foundations', function (): void {
    $root = dirname(__DIR__, 5);
    $mediaComposer = json_decode((string) file_get_contents($root . '/packages/zoosper-media/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $pageComposer = json_decode((string) file_get_contents($root . '/app/zoosper-page/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $pageServices = (string) file_get_contents($root . '/app/zoosper-page/config/services.php');
    $pageControllers = (string) file_get_contents($root . '/app/zoosper-page/config/controllers.php');
    $renderer = (string) file_get_contents($root . '/app/zoosper-page/src/Service/PageRenderer.php');

    expect($mediaComposer['require'])->not->toHaveKey('zoosper/admin')
        ->and($pageComposer['require'])->toHaveKey('zoosper/media', 'dev-dev')
        ->and($pageServices)->toContain('EditorJsImageBlockSanitizer')
        ->toContain('BlockJsonToHtmlRenderer')
        ->and($pageControllers)->toContain('AdminLayoutRendererInterface')
        ->toContain('AdminViewRendererInterface')
        ->and($renderer)->toContain('renderContent(')
        ->toContain('$page->hasBlockJson()')
        ->toContain('json_decode((string) $page->contentJson, true)')
        ->toContain("return trim(\$html) !== '' ? \$html : \$page->content;");
});

it('keeps Media source free of concrete Admin imports', function (): void {
    $root = dirname(__DIR__, 5);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $root . '/packages/zoosper-media/src',
        FilesystemIterator::SKIP_DOTS,
    ));
    $offenders = [];
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $source = (string) file_get_contents($file->getPathname());
        if (str_contains($source, 'use Zoosper\\Admin\\')) {
            $offenders[] = $file->getPathname();
        }
    }
    expect($offenders)->toBe([]);
});
