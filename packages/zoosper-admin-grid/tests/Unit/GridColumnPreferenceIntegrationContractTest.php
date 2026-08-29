<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

it('requires each production save-columns handler to forward complete column state', function (): void {
    $root = dirname(__DIR__, 4);
    $paths = [
        $root . '/app/zoosper-page/src/Admin/PageGridMutationHandler.php',
        $root . '/packages/zoosper-store-orders/src/Admin/StoreOrderGridMutationHandler.php',
    ];

    foreach ($paths as $path) {
        $source = (string) file_get_contents($path);
        expect($source)
            ->toContain('GridWorkspaceMutationContract::SAVE_COLUMNS')
            ->toContain('saveColumnPreferences(')
            ->toContain("\$post['visible_columns']")
            ->toContain("\$post['column_order']")
            ->not->toContain('SAVE_COLUMNS => $this->mutations->saveVisibleColumns(');
    }
});
