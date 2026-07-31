<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Unit\Admin;

use Zoosper\Grid\GridDataSourceInterface;
use Zoosper\Page\Admin\PageGridDataSource;
use Zoosper\Page\Admin\PageGridDefinition;

test('page shared-grid foundation exposes stable contracts for final cutover', function (): void {
    expect(PageGridDefinition::KEY)->toBe('admin.pages');
    expect(is_subclass_of(PageGridDataSource::class, GridDataSourceInterface::class))->toBeTrue();
});

test('page template cutover uses shared grid output', function (): void {
    $basePath = dirname(__DIR__, 5);
    $template = (string) file_get_contents(
        $basePath . '/app/zoosper-page/resources/views/admin/pages/index.php',
    );

    expect($template)->toContain('$gridHtml');
    expect($template)->not->toContain('<table>');
});
