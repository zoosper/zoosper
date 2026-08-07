<?php

declare(strict_types=1);

it('locks the completed Page runtime ownership boundaries', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $factory = (string) file_get_contents($root . '/app/zoosper-page/config/controllers.php');
    expect($controller)->toContain('PageAdminGridResponder')
        ->toContain('PageAdminPreviewResponder')
        ->toContain('PageAdminFormRenderer')
        ->toContain('PageSaveCoordinator')
        ->toContain('PagePublicationCoordinator')
        ->not->toContain('PageGridQueryState::fromQuery')
        ->not->toContain('new GridWorkspaceRequest(')
        ->not->toContain('new AdminFormConfigAggregator(')
        ->not->toContain('PageEvents::PUBLISHED')
        ->and($factory)->toContain('gridResponder: new PageAdminGridResponder(')
        ->toContain('previewResponder: new PageAdminPreviewResponder(')
        ->toContain('formRenderer: new PageAdminFormRenderer(')
        ->toContain('pageSaver: new PageSaveCoordinator(')
        ->toContain('publication: new PagePublicationCoordinator(');
});

it('keeps Page runtime free of superglobal request access', function (): void {
    $root = dirname(__DIR__, 5);
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
        $root . '/app/zoosper-page/src', FilesystemIterator::SKIP_DOTS,
    ));
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $source = (string) file_get_contents($file->getPathname());
        expect($source, $file->getPathname())
            ->not->toContain('$_GET')
            ->not->toContain('$_POST')
            ->not->toContain('$_SERVER');
    }
});
