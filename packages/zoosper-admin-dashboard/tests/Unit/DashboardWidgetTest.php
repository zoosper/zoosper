<?php

declare(strict_types=1);

use Zoosper\AdminDashboard\DashboardRole;
use Zoosper\AdminDashboard\DashboardRolePreference;
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

it('keeps role identity and role Dashboard preferences immutable and validated', function (): void {
    $role = new DashboardRole(7, 'content_manager', 'Content Manager');
    $preference = new DashboardRolePreference($role->id, $role->code, ['finance.total'], ['content.drafts', 'finance.total']);

    expect($role->label)->toBe('Content Manager')
        ->and($preference->hiddenWidgetCodes)->toBe(['finance.total'])
        ->and($preference->widgetOrder)->toBe(['content.drafts', 'finance.total']);
});

it('rejects duplicate role Dashboard preference codes', function (): void {
    expect(fn (): DashboardRolePreference => new DashboardRolePreference(1, 'finance', ['one', 'one'], ['one']))
        ->toThrow(InvalidArgumentException::class);
});











