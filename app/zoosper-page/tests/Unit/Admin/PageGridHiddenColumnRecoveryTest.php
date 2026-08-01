<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

test('hidden columns remain available in the Columns chooser for recovery', function (): void {
    $root = dirname(__DIR__, 5);
    $source = (string) file_get_contents(
        $root . '/app/zoosper-page/src/Admin/PageGridWorkspace.php',
    );

    expect($source)
        ->toContain('$workspaceDefinition = ($this->columnOrderer ?? new GridColumnOrderer())')
        ->toContain('->apply($this->definition->build(), $state->columnOrder)')
        ->toContain('definition: $workspaceDefinition')
        ->toContain("'state' => \$state")
        ->toContain("'html' => \$this->renderer->render(\$workspaceState, self::ACTION)");
});
