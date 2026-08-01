<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('explicit column state is validated against the complete definition', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents(
        $root . '/app/zoosper-page/src/Admin/PageGridWorkspace.php',
    );

    expect($source)
        ->toContain('$completeDefinition = $this->definition->build();')
        ->toContain('$known = $completeDefinition->allColumnKeys();')
        ->toContain('foreach ($completeDefinition->columns as $column)')
        ->toContain('->apply($completeDefinition, $order)');

    expect($source)
        ->not->toContain('$known = $state->definition->allColumnKeys();')
        ->not->toContain('->apply($state->definition, $order)');
});
