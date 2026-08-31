<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Page\Admin\PageSiteFilterSql;

test('multiple Site IDs become individually bound placeholders', function (): void {
    $filter = (new PageSiteFilterSql())->build(['9', '4', '9']);

    expect($filter)->toBe([
        'sql' => 'p.site_id IN (:site_id_0, :site_id_1)',
        'parameters' => ['site_id_0' => 9, 'site_id_1' => 4],
    ]);
});

test('invalid Site values never enter SQL', function (): void {
    $filter = (new PageSiteFilterSql())->build([
        '4) OR 1=1 --',
        '-1',
        '0',
        '',
    ]);

    expect($filter)->toBe(['sql' => '', 'parameters' => []]);
});










