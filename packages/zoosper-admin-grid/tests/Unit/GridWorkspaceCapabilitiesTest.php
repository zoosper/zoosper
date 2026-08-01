<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspaceCapabilities;

test('modern admin grid workspace enables the complete interaction set by default', function (): void {
    $capabilities = new GridWorkspaceCapabilities();

    expect($capabilities->filters)->toBeTrue();
    expect($capabilities->columnVisibility)->toBeTrue();
    expect($capabilities->columnOrdering)->toBeTrue();
    expect($capabilities->bookmarks)->toBeTrue();
    expect($capabilities->csvExport)->toBeTrue();
});
