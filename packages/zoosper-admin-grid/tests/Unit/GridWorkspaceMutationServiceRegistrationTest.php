<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid\Tests\Unit;

use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\AdminGrid\GridWorkspaceMutationGuard;

it('registers shared workspace mutation collaborators for module consumers', function (): void {
    $root = dirname(__DIR__, 4);
    $services = require $root . '/packages/zoosper-admin-grid/config/services.php';

    expect($services)->toHaveKeys([
        GridWorkspaceMutationGuard::class,
        GridWorkspaceMutationFormsRenderer::class,
    ]);
});
