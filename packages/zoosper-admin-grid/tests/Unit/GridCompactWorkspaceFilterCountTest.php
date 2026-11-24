<?php

declare(strict_types=1);

use Zoosper\AdminGrid\GridCompactWorkspaceRenderer;
use Zoosper\AdminGrid\GridViewState;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;
use Zoosper\Pagination\Pager;

it('counts normalized query filters including zero and multiselects', function (): void {
    $definition = new GridDefinition('Items', [new GridColumn('id', 'ID')], [
        new GridFilter('zero', 'Zero'),
        new GridFilter('blank', 'Blank'),
        new GridFilter('malformed', 'Malformed'),
        new GridFilter('choices', 'Choices', 'multiselect'),
    ]);
    $criteria = GridCriteria::fromValues([
        'zero' => '0', 'blank' => '  ', 'malformed' => ['ignored'], 'choices' => ['one', 'two'],
    ], $definition);
    $state = new GridViewState($definition, $criteria, ['id'], ['id'], []);

    expect((new GridCompactWorkspaceRenderer())->render($state, '/admin/items'))
        ->toContain('aria-expanded="false">Filters (2)</button>');
});

it('retains the active count for direct runtime scalar and array values', function (): void {
    $definition = new GridDefinition('Items', [new GridColumn('id', 'ID')]);
    $criteria = new GridCriteria(new Pager(1, 20), null, 'asc', [
        'zero' => 0, 'false' => false, 'null' => null, 'empty' => [], 'array' => [null],
    ]);
    $state = new GridViewState($definition, $criteria, ['id'], ['id'], []);

    expect((new GridCompactWorkspaceRenderer())->render($state, '/admin/items'))
        ->toContain('aria-expanded="false">Filters (2)</button>');
});