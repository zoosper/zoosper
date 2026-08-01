<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Grid\GridFilter;
use Zoosper\Grid\GridFilterOption;

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
