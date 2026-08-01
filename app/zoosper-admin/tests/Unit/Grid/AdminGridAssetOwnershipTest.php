<?php

declare(strict_types=1);

namespace Zoosper\Admin\Tests\Unit\Grid;

test('compact Grid source assets are owned by the Admin module', function (): void {
    $root = dirname(__DIR__, 5);

    expect(is_file($root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-compact.css'))->toBeTrue();
    expect(is_file($root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-compact.js'))->toBeTrue();
    expect(is_file($root . '/app/zoosper-admin/resources/assets/css/zoosper-grid-columns.css'))->toBeTrue();
    expect(is_file($root . '/app/zoosper-admin/resources/assets/js/zoosper-grid-columns.js'))->toBeTrue();

    expect(is_file($root . '/public/assets/admin/css/zoosper-grid-compact.css'))->toBeFalse();
    expect(is_file($root . '/public/assets/admin/js/zoosper-grid-compact.js'))->toBeFalse();
});
