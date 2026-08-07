<?php

declare(strict_types=1);

it('keeps PageAdminController thin after Grid form save publication and preview extraction', function (): void {
    $root = dirname(__DIR__, 5);
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $factory = (string) file_get_contents($root . '/app/zoosper-page/config/controllers.php');
    expect($controller)->toContain('PageAdminGridResponder')
        ->toContain('PageAdminPreviewResponder')
        ->not->toContain('PageGridQueryState::fromQuery')
        ->not->toContain('GridCriteria::fromValues')
        ->not->toContain('renderBody(')
        ->not->toContain('new GridBulkActionManifest(')
        ->not->toContain('new GridWorkspaceRequest(')
        ->not->toContain('$this->renderer->render(')
        ->not->toContain('<h1>Page not found</h1>')
        ->and($factory)->toContain('gridResponder: new PageAdminGridResponder(')
        ->toContain('previewResponder: new PageAdminPreviewResponder(');
});
