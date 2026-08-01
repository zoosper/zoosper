<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use ReflectionClass;
use ReflectionUnionType;
use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;
use Zoosper\AdminGrid\GridWorkspaceRenderer;
use Zoosper\Page\Admin\PageGridWorkspace;

test('Page Grid workspace accepts both standard and compact shared renderers', function (): void {
    $constructor = (new ReflectionClass(PageGridWorkspace::class))->getConstructor();
    $renderer = $constructor?->getParameters()[2] ?? null;
    $type = $renderer?->getType();

    expect($type)->toBeInstanceOf(ReflectionUnionType::class);

    $types = array_map(
        static fn ($named): string => $named->getName(),
        $type->getTypes(),
    );

    expect($types)->toContain(GridWorkspaceRenderer::class)
        ->toContain(GridCompactWorkspaceRenderer::class);
});
