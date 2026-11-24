<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Grid\GridFilter;
use Zoosper\Grid\GridFilterOption;
use Zoosper\Page\Admin\PageSiteFilterOptions;
use Zoosper\Site\Repository\SiteRepository;

/** Contract test for the Pages Site filter presentation. */
test('Pages Site filter is a named multiselect rather than a raw ID field', function (): void {
    $filter = new GridFilter('site_id', 'Site', 'multiselect', [
        new GridFilterOption('4', 'Main Website'),
        new GridFilterOption('9', 'Wholesale Portal'),
    ]);
    expect($filter->label)->toBe('Site');
    expect($filter->type)->toBe('multiselect');
    expect(array_map(static fn (GridFilterOption $option): string => $option->label, $filter->normalisedOptions()))
        ->toBe(['Main Website', 'Wholesale Portal']);
});

test('site filter options map active sites in repository order', function (): void {
    $pdo = new \PDO('sqlite::memory:');
    $pdo->exec('CREATE TABLE sites (id INTEGER PRIMARY KEY, code TEXT, name TEXT, status TEXT)');
    $options = new PageSiteFilterOptions(new SiteRepository($pdo));

    expect($options->all())->toBe([]);

    $insert = $pdo->prepare('INSERT INTO sites (id, code, name, status) VALUES (?, ?, ?, ?)');
    foreach ([
        [2, 'second', 'Zebra', 'active'],
        [1, 'first', ' Alpha ', 'active'],
        [3, 'blank', '   ', 'active'],
        [0, 'zero', 'Zero', 'active'],
        [-1, 'negative', 'Negative', 'active'],
        [4, 'inactive', 'Inactive', 'inactive'],
    ] as $row) {
        $insert->execute($row);
    }

    $result = $options->all();
    expect(array_map(static fn (GridFilterOption $option): array => [$option->value, $option->label], $result))
        ->toBe([['1', 'Alpha'], ['2', 'Zebra']]);
});










