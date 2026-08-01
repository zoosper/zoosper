<?php

declare(strict_types=1);

namespace Zoosper\Grid\Tests\Unit;

use Zoosper\Grid\GridFilter;
use Zoosper\Grid\GridFilterOption;
use Zoosper\Grid\GridFilterValue;

test('multiselect filter exposes human-readable options', function (): void {
    $filter = new GridFilter('site_id', 'Site', 'multiselect', [
        new GridFilterOption('1', 'Main Website'),
        ['value' => '2', 'label' => 'Wholesale Portal'],
    ]);
    expect(array_map(static fn (GridFilterOption $option): array => [$option->value, $option->label], $filter->normalisedOptions()))
        ->toBe([['1', 'Main Website'], ['2', 'Wholesale Portal']]);
});

test('multiselect values are trimmed, de-duplicated and empty values removed', function (): void {
    expect(GridFilterValue::many([' 2 ', '1', '2', '', null]))->toBe(['2', '1']);
});

test('unsupported filter types fail loudly', function (): void {
    expect(fn (): GridFilter => new GridFilter('site_id', 'Site', 'unknown'))
        ->toThrow(\InvalidArgumentException::class, 'Unsupported grid filter type');
});
