<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Page\Admin\PageGridExportCriteria;
use Zoosper\Page\Admin\PageGridExportSqlBuilder;

test('export SQL uses bound search status and multiple Site parameters', function (): void {
    $plan = (new PageGridExportSqlBuilder())->build(new PageGridExportCriteria(
        search: 'landing',
        status: 'published',
        siteIds: [9, 4],
        sortBy: 'title',
        sortDir: 'asc',
    ));

    expect($plan->whereSql)
        ->toContain('p.title LIKE :export_search')
        ->toContain('p.status = :export_status')
        ->toContain('p.site_id IN (:export_site_id_0, :export_site_id_1)');
    expect($plan->orderSql)->toBe('ORDER BY p.title ASC');
    expect($plan->parameters)->toBe([
        'export_search' => '%landing%',
        'export_status' => 'published',
        'export_site_id_0' => 9,
        'export_site_id_1' => 4,
    ]);
});

test('sort column and direction are allow-listed', function (): void {
    $plan = (new PageGridExportSqlBuilder())->build(new PageGridExportCriteria(
        search: '',
        status: '',
        siteIds: [],
        sortBy: 'title; DROP TABLE pages',
        sortDir: 'sideways',
    ));

    expect($plan->whereSql)->toBe('');
    expect($plan->orderSql)->toBe('ORDER BY p.id DESC');
    expect($plan->parameters)->toBe([]);
});

test('hostile Site values cannot enter the SQL plan', function (): void {
    $criteria = new PageGridExportCriteria('', '', [4, 9], 'id', 'desc');
    $plan = (new PageGridExportSqlBuilder())->build($criteria);

    expect($plan->whereSql)->toBe('WHERE p.site_id IN (:export_site_id_0, :export_site_id_1)');
    expect($plan->whereSql)->not->toContain('4, 9');
});










