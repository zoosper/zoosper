<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Page\Admin\PageGridDefinition;

test('page grid definition uses the shared configurable grid contract', function (): void {
    $definition = (new PageGridDefinition())->build();

    expect($definition->title)->toBe('Pages');
    expect($definition->allColumnKeys())->toBe([
        'id', 'title', 'slug', 'status', 'site_name', 'actions',
    ]);
    expect($definition->sortableColumnKeys())->toBe(['id', 'title', 'slug', 'status']);
    expect($definition->filterKeys())->toBe(['q', 'title', 'slug', 'status', 'site_id']);
    expect($definition->defaultSort)->toBe('id');
    expect($definition->defaultSortDir)->toBe('desc');
});

test('page actions renderer escapes the route surface by casting the id', function (): void {
    $definition = (new PageGridDefinition())->build();
    $actions = $definition->columns[array_key_last($definition->columns)];
    $html = $actions->renderValue(null, ['id' => '7<script>alert(1)</script>']);

    expect($html)->toContain('/admin/pages/edit?id=7');
    expect($html)->not->toContain('<script>');
    expect($html)->toContain('rel="noopener"');
});

test('module grid registry can add a page column by the stable page grid key', function (): void {
    $basePath = dirname(__DIR__, 5);
    $root = sys_get_temp_dir() . '/zoosper-page-grid-' . bin2hex(random_bytes(6));
    $module = $root . '/app/acme-page-extra';
    mkdir($module . '/config', 0775, true);
    file_put_contents($module . '/composer.json', json_encode([
        'name' => 'acme/page-extra',
        'type' => 'zoosper-module',
        'extra' => ['marko' => ['module' => true]],
    ], JSON_THROW_ON_ERROR));
    file_put_contents($module . '/module.php', "<?php return [];\n");
    file_put_contents($module . '/config/grid_columns.php', <<<'PHP'
<?php
use Zoosper\Grid\GridColumn;
return ['admin.pages' => ['columns' => [new GridColumn('updated_at', 'Updated')], 'filters' => []]];
PHP);

    $registry = new GridColumnRegistry(new ModuleRegistry($root));
    $definition = (new PageGridDefinition($registry))->build();

    expect($definition->allColumnKeys())->toContain('updated_at');
});
