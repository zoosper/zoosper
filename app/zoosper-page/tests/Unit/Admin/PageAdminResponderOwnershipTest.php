<?php

declare(strict_types=1);

it('keeps Grid and preview orchestration in Page-owned responders', function (): void {
    $root = dirname(__DIR__, 5);
    $grid = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/PageAdminGridResponder.php');
    $preview = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/PageAdminPreviewResponder.php');
    expect($grid)->toContain('PageGridQueryState::fromQuery($query)')
        ->toContain('new GridBulkActionManifest(')
        ->toContain("'zoosper-page::admin/pages/index'")
        ->toContain('new GridWorkspaceRequest(')
        ->and($preview)->toContain('$this->renderer->render($page, $site)')
        ->toContain('Page not found')
        ->toContain('Site not found');
});
