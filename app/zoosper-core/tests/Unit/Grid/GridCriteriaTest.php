<?php

declare(strict_types=1);

use Zoosper\Core\Grid\GridColumn;
use Zoosper\Core\Grid\GridCriteria;
use Zoosper\Core\Grid\GridDefinition;
use Zoosper\Core\Grid\GridFilter;

/*
 * Grid Core (Phase A) behavioural tests: GridCriteria parsing, sort validation,
 * filter extraction, and link-parameter round-tripping.
 */

function sampleGridDefinition(): GridDefinition
{
    return new GridDefinition(
        title: 'Sample',
        columns: [
            new GridColumn('created_at', 'Time', sortable: true),
            new GridColumn('email', 'Email', sortable: false),
        ],
        filters: [
            new GridFilter('q', 'Search'),
            new GridFilter('status', 'Status', type: 'select', options: [
                ['value' => 'success', 'label' => 'Success'],
                ['value' => 'failed', 'label' => 'Failed'],
            ]),
        ],
        defaultSort: 'created_at',
        defaultSortDir: 'desc',
    );
}

it('falls back to the definition default sort when none is requested', function (): void {
    $criteria = GridCriteria::fromValues([], sampleGridDefinition());

    expect($criteria->sortBy)->toBe('created_at')
        ->and($criteria->sortDir)->toBe('desc');
});

it('accepts a requested sort only when the column is declared sortable', function (): void {
    $definition = sampleGridDefinition();

    $valid = GridCriteria::fromValues(['sort' => 'created_at', 'dir' => 'asc'], $definition);
    expect($valid->sortBy)->toBe('created_at')
        ->and($valid->sortDir)->toBe('asc');

    // 'email' is declared but NOT sortable -> falls back to the definition's
    // coherent default (column, direction) pair; the requested 'asc' is
    // discarded since it was paired with an invalid sort column.
    $invalid = GridCriteria::fromValues(['sort' => 'email', 'dir' => 'asc'], $definition);
    expect($invalid->sortBy)->toBe('created_at')
        ->and($invalid->sortDir)->toBe('desc');

    // An unknown column name is ignored entirely.
    $unknown = GridCriteria::fromValues(['sort' => 'nonexistent'], $definition);
    expect($unknown->sortBy)->toBe('created_at');
});

it('only extracts filter values that are declared on the definition', function (): void {
    $definition = sampleGridDefinition();

    $criteria = GridCriteria::fromValues([
        'q' => 'alice',
        'status' => 'failed',
        'not_a_real_filter' => 'ignored',
    ], $definition);

    expect($criteria->filters)->toBe(['q' => 'alice', 'status' => 'failed'])
        ->and($criteria->filters)->not->toHaveKey('not_a_real_filter');
});

it('omits empty filter values entirely rather than storing blanks', function (): void {
    $criteria = GridCriteria::fromValues(['q' => '   ', 'status' => 'failed'], sampleGridDefinition());

    expect($criteria->filters)->toBe(['status' => 'failed']);
});

it('preserves page_size, sort, and filters in linkParameters()', function (): void {
    $criteria = GridCriteria::fromValues(['page_size' => '10', 'q' => 'bob'], sampleGridDefinition());

    $params = $criteria->linkParameters();

    expect($params['page_size'])->toBe(10)
        ->and($params['sort'])->toBe('created_at')
        ->and($params['dir'])->toBe('desc')
        ->and($params['q'])->toBe('bob');
});

it('toggles sort direction only for the currently active column', function (): void {
    $criteria = GridCriteria::fromValues(['sort' => 'created_at', 'dir' => 'asc'], sampleGridDefinition());

    expect($criteria->toggledSortDir('created_at'))->toBe('desc') // flips from current asc
        ->and($criteria->toggledSortDir('other_column', 'asc'))->toBe('asc'); // not active -> default
});
