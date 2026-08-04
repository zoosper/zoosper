<?php

declare(strict_types=1);

use Zoosper\Grid\BulkAction\GridBulkActionDefinition;
use Zoosper\Grid\BulkAction\GridBulkActionRegistry;
use Zoosper\Grid\BulkAction\GridBulkConfirmationPolicy;
use Zoosper\Grid\BulkAction\GridBulkExecutionType;
use Zoosper\Grid\BulkAction\GridBulkSelection;
use Zoosper\Grid\BulkAction\GridBulkSelectionScope;

it('registers actions per Grid without cross-Grid leakage', function (): void {
    $registry = new GridBulkActionRegistry();
    $action = new GridBulkActionDefinition(
        id: 'export.selected',
        label: 'Export selected',
        selectionScope: GridBulkSelectionScope::EXPLICIT_IDENTITIES,
        executionType: GridBulkExecutionType::CLIENT_DOWNLOAD,
    );
    $registry->register('admin.pages', $action);
    expect($registry->allForGrid('admin.pages'))->toHaveCount(1)
        ->and($registry->allForGrid('store.orders'))->toBe([])
        ->and($registry->require('admin.pages', 'export.selected'))->toBe($action);
});

it('rejects duplicate action IDs inside one Grid', function (): void {
    $registry = new GridBulkActionRegistry();
    $action = new GridBulkActionDefinition('export.selected', 'Export selected', GridBulkSelectionScope::CURRENT_PAGE, GridBulkExecutionType::CLIENT_DOWNLOAD);
    $registry->register('admin.pages', $action);
    expect(fn () => $registry->register('admin.pages', $action))->toThrow(InvalidArgumentException::class, 'already registered');
});

it('requires confirmation for server and remote mutations', function (GridBulkExecutionType $type): void {
    expect(fn () => new GridBulkActionDefinition('page.publish', 'Publish', GridBulkSelectionScope::EXPLICIT_IDENTITIES, $type))
        ->toThrow(InvalidArgumentException::class, 'confirmation');
})->with([GridBulkExecutionType::SERVER_MUTATION, GridBulkExecutionType::REMOTE_MUTATION]);

it('normalises unique identities and enforces the action maximum', function (): void {
    $selection = new GridBulkSelection([3, '3', 2, 1], 3);
    expect($selection->identities)->toBe(['3', '2', '1'])->and($selection->count())->toBe(3);
    expect(fn () => new GridBulkSelection([1, 2, 3], 2))->toThrow(InvalidArgumentException::class, 'maximum');
});
