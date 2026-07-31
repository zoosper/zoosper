<?php

declare(strict_types=1);

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;
use Zoosper\Core\Module\ModuleRegistry;

/*
 * Phase B2 behavioural tests for the grid extensibility mechanism
 * (GridColumnRegistry + GridDefinition::withAdditionalColumns/Filters).
 *
 * Uses real temp module directories (created and torn down per test),
 * mirroring the fixture technique in ModuleRegistryMemoizationTest, so this
 * proves the ACTUAL discovery path (ModuleRegistry -> module path ->
 * config/grid_columns.php) rather than a mocked shortcut.
 */

function removeGridFixtureDir(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    $items = scandir($dir) ?: [];
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? removeGridFixtureDir($path) : unlink($path);
    }
    rmdir($dir);
}

/**
 * Create a temp base path with a single enabled module whose
 * config/grid_columns.php contains the given PHP source (a `return [...]`
 * expression body).
 */
function makeGridContributorModule(string $moduleName, string $grid, string $columnsPhpSource): string
{
    $base = sys_get_temp_dir() . '/zoosper-grid-registry-test-' . bin2hex(random_bytes(8));
    $moduleDir = $base . '/app/' . $moduleName;
    mkdir($moduleDir . '/config', 0775, true);

    file_put_contents(
        $moduleDir . '/module.php',
        "<?php\ndeclare(strict_types=1);\nreturn ['name' => '{$moduleName}', 'enabled' => true, 'sort_order' => 100];\n",
    );

    file_put_contents(
        $moduleDir . '/config/grid_columns.php',
        "<?php\ndeclare(strict_types=1);\nuse Zoosper\\Grid\\GridColumn;\nuse Zoosper\\Grid\\GridFilter;\nreturn ['{$grid}' => {$columnsPhpSource}];\n",
    );

    return $base;
}

function baseDefinitionForTests(): GridDefinition
{
    return new GridDefinition(
        title: 'Test Grid',
        columns: [new GridColumn('created_at', 'Time', sortable: true)],
        filters: [new GridFilter('q', 'Search')],
        defaultSort: 'created_at',
    );
}

it('leaves the base definition unchanged when no module contributes to this grid key', function (): void {
    $base = makeGridContributorModule(
        'contributor-a',
        'some-other-grid', // deliberately NOT the grid key we query
        "['columns' => [new GridColumn('x', 'X')], 'filters' => []]",
    );

    try {
        $registry = new GridColumnRegistry(new ModuleRegistry($base));
        $result = $registry->apply('test-grid', baseDefinitionForTests());

        expect($result->columns)->toHaveCount(1)
            ->and($result->columns[0]->key)->toBe('created_at');
    } finally {
        removeGridFixtureDir($base);
    }
});

it('merges a column contributed by a DIFFERENT module into the target grid', function (): void {
    $base = makeGridContributorModule(
        'contributor-b',
        'test-grid',
        "['columns' => [new GridColumn('extra_field', 'Extra Field')], 'filters' => []]",
    );

    try {
        $registry = new GridColumnRegistry(new ModuleRegistry($base));
        $result = $registry->apply('test-grid', baseDefinitionForTests());

        $keys = array_map(static fn ($c) => $c->key, $result->columns);
        expect($keys)->toBe(['created_at', 'extra_field']);
    } finally {
        removeGridFixtureDir($base);
    }
});

it('merges a filter contributed by a module into the target grid', function (): void {
    $base = makeGridContributorModule(
        'contributor-c',
        'test-grid',
        "['columns' => [], 'filters' => [new GridFilter('extra_filter', 'Extra Filter')]]",
    );

    try {
        $registry = new GridColumnRegistry(new ModuleRegistry($base));
        $result = $registry->apply('test-grid', baseDefinitionForTests());

        expect($result->filterKeys())->toBe(['q', 'extra_filter']);
    } finally {
        removeGridFixtureDir($base);
    }
});

it('does NOT let an extending module override an existing column key', function (): void {
    // Attempts to redeclare 'created_at' (the base grid's own column) with a
    // different label — the base definition's column must win.
    $base = makeGridContributorModule(
        'contributor-d',
        'test-grid',
        "['columns' => [new GridColumn('created_at', 'Hijacked Label')], 'filters' => []]",
    );

    try {
        $registry = new GridColumnRegistry(new ModuleRegistry($base));
        $result = $registry->apply('test-grid', baseDefinitionForTests());

        expect($result->columns)->toHaveCount(1)
            ->and($result->columns[0]->label)->toBe('Time'); // unchanged, not 'Hijacked Label'
    } finally {
        removeGridFixtureDir($base);
    }
});

it('an unrelated grid definition (using GridDefinition directly) supports the same merge contract', function (): void {
    // Direct unit test of GridDefinition::withAdditionalColumns/Filters,
    // independent of module discovery, proving the merge contract itself.
    $definition = baseDefinitionForTests();

    $merged = $definition
        ->withAdditionalColumns([new GridColumn('bonus', 'Bonus')])
        ->withAdditionalFilters([new GridFilter('bonus_filter', 'Bonus Filter')]);

    expect(array_map(static fn ($c) => $c->key, $merged->columns))->toBe(['created_at', 'bonus'])
        ->and($merged->filterKeys())->toBe(['q', 'bonus_filter'])
        // Original definition is untouched (immutability).
        ->and($definition->columns)->toHaveCount(1);
});

it('GridColumnRegistry caches discovery per instance, like ModuleRegistry itself', function (): void {
    $base = makeGridContributorModule(
        'contributor-e',
        'test-grid',
        "['columns' => [new GridColumn('cached_field', 'Cached Field')], 'filters' => []]",
    );

    try {
        $registry = new GridColumnRegistry(new ModuleRegistry($base));

        $first = $registry->apply('test-grid', baseDefinitionForTests());
        expect($first->columns)->toHaveCount(2);

        // Delete the contributing module's config file entirely.
        unlink($base . '/app/contributor-e/config/grid_columns.php');

        $second = $registry->apply('test-grid', baseDefinitionForTests());

        // Still 2 — proves discovery was cached, not re-read from disk.
        expect($second->columns)->toHaveCount(2);
    } finally {
        removeGridFixtureDir($base);
    }
});

