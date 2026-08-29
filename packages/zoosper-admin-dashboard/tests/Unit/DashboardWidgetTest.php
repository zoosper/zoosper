<?php

declare(strict_types=1);

use Zoosper\AdminDashboard\DashboardWidget;

it('normalises a safe immutable Dashboard widget', function (): void {
    $widget = new DashboardWidget(' auth.active-users ', ' Active users ', ' 3 ', ' Enabled accounts ', 20);

    expect($widget->code)->toBe('auth.active-users')
        ->and($widget->title)->toBe('Active users')
        ->and($widget->value)->toBe('3')
        ->and($widget->description)->toBe('Enabled accounts')
        ->and($widget->sortOrder)->toBe(20);
});

it('rejects empty and unsafe widget identities', function (string $code): void {
    expect(fn (): DashboardWidget => new DashboardWidget($code, 'Title', '1', 'Description'))
        ->toThrow(InvalidArgumentException::class);
})->with(['', 'Unsafe Code', 'javascript:bad']);
