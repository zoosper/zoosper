<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridBulkActionManifestRenderer;
use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkActionManifest;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;

it('renders an inert escaped JSON bulk-action manifest', function (): void {
    $manifest = new GridBulkActionManifest('admin.pages', [
        new GridBulkActionDefinition('export.selected', 'Export <selected>', GridBulkSelectionScope::EXPLICIT_IDENTITIES, GridBulkExecutionType::CLIENT_DOWNLOAD),
    ]);
    $html = (new GridBulkActionManifestRenderer())->render($manifest);
    expect($html)->toContain('type="application/json"')
        ->toContain('data-grid-bulk-action-manifest')
        ->toContain('\\u003Cselected\\u003E')
        ->not->toContain('<selected>');
});
