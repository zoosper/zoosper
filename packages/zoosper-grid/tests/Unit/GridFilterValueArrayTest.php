<?php

declare(strict_types=1);

namespace Zoosper\Grid\Tests\Unit;

use Zoosper\Grid\GridFilterValue;

test('filter values safely normalise nested lists and reject arrays as scalars', function (): void {
    expect(GridFilterValue::many([['4', '9'], '4', '']))->toBe(['4', '9']);
    expect(GridFilterValue::one(['4']))->toBe('');
});











