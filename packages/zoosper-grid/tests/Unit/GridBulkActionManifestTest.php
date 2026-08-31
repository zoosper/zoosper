<?php

declare(strict_types=1);

use Zoosper\Grid\BulkAction\GridBulkActionAuthoriser;
use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkActionManifest;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;

it('omits required permissions from the browser manifest', function (): void {
    $definition = new GridBulkActionDefinition(
        'export.selected',
        'Export selected',
        GridBulkSelectionScope::EXPLICIT_IDENTITIES,
        GridBulkExecutionType::CLIENT_DOWNLOAD,
        requiredPermission: 'page.manage',
    );
    $manifest = new GridBulkActionManifest('admin.pages', [$definition]);
    $payload = $manifest->jsonSerialize();
    expect($payload['actions'][0])->not->toHaveKey('requiredPermission')
        ->and($payload['actions'][0]['id'])->toBe('export.selected');
});

it('filters unauthorised definitions before presentation', function (): void {
    $definitions = [
        new GridBulkActionDefinition('export.selected', 'Export selected', GridBulkSelectionScope::EXPLICIT_IDENTITIES, GridBulkExecutionType::CLIENT_DOWNLOAD),
        new GridBulkActionDefinition('page.secret', 'Secret', GridBulkSelectionScope::EXPLICIT_IDENTITIES, GridBulkExecutionType::SERVER_DOWNLOAD, requiredPermission: 'secret.manage'),
    ];
    $allowed = (new GridBulkActionAuthoriser())->authorised($definitions, static fn (string $permission): bool => false);
    expect($allowed)->toHaveCount(1)->and($allowed[0]->id)->toBe('export.selected');
});











