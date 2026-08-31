<?php

declare(strict_types=1);

use Zoosper\Page\Admin\PageGridBulkActions;

it('activates Publish selected in the Page browser manifest', function (): void {
    $ids = array_map(static fn ($definition): string => $definition->id, PageGridBulkActions::definitions());
    expect($ids)->toBe(['export.selected', 'page.publish']);
});

it('posts selected Page identities through the protected endpoint', function (): void {
    $root = dirname(__DIR__, 5);
    $script = file_get_contents($root . '/packages/zoosper-admin-grid/resources/admin/js/grid-server-mutation.js');
    expect($script)->not->toBeFalse();
    expect($script)->toContain("add('_csrf_token', token)");
    expect($script)->toContain("add('bulk_action', definition.id)");
    expect($script)->toContain("add('confirmed_action', definition.id)");
    expect($script)->toContain("add('selected_ids[]', identity)");
    expect($script)->toContain('window.confirm');
});










