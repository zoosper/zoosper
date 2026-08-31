<?php

declare(strict_types=1);

use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Media\Controller\MediaAdminController;

it('contains zero direct "use Zoosper\Admin\" imports anywhere in zoosper-media/src', function (): void {
    $basePath = dirname(__DIR__, 5);
    $srcPath = $basePath . '/packages/zoosper-media/src';
    $offendingFiles = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcPath, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') continue;
        $contents = file_get_contents($file->getPathname());
        if ($contents !== false && preg_match('/use\s+Zoosper\Admin\/', $contents) === 1) {
            $offendingFiles[] = str_replace($basePath . '/', '', $file->getPathname());
        }
    }
    expect($offendingFiles)->toBe([], 'Found direct Zoosper\Admin\ imports in: ' . implode(', ', $offendingFiles));
});

it('confirms media composer.json no longer requires zoosper/admin', function (): void {
    $basePath = dirname(__DIR__, 5);
    $composerJson = json_decode((string) file_get_contents($basePath . '/packages/zoosper-media/composer.json'), true);
    expect($composerJson['require'])->not->toHaveKey('zoosper/admin');
});

it('confirms MediaAdminController no longer has a layout constructor parameter', function (): void {
    $constructor = (new ReflectionClass(MediaAdminController::class))->getConstructor();
    $paramNames = array_map(static fn (ReflectionParameter $p): string => $p->getName(), $constructor->getParameters());
    expect($paramNames)->not->toContain('layout');
});

it('confirms MediaAdminController depends on AdminViewRendererInterface, not the concrete AdminViewRenderer', function (): void {
    $constructor = (new ReflectionClass(MediaAdminController::class))->getConstructor();
    $viewsParam = null;
    foreach ($constructor->getParameters() as $parameter) {
        if ($parameter->getName() === 'views') $viewsParam = $parameter;
    }
    expect($viewsParam)->not->toBeNull();
    expect((string) $viewsParam->getType())->toContain(AdminViewRendererInterface::class);
});

it('confirms AdminViewRenderer implements the new interface', function (): void {
    expect(is_subclass_of(AdminViewRenderer::class, AdminViewRendererInterface::class))->toBeTrue();
});











