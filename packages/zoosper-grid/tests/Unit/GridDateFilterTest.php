<?php

declare(strict_types=1);

namespace Zoosper\Grid\Tests\Unit;

use Zoosper\Grid\GridFilter;

it('supports a declarative native date filter type', function (): void {
    $filter = new GridFilter('placed_from', 'Placed From', 'date');
    expect($filter->type)->toBe('date');
});











