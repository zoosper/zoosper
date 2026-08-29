<?php

declare(strict_types=1);

use Zoosper\Admin\Dashboard\DashboardPreference;
use Zoosper\Admin\Dashboard\DashboardWidgetCollection;
use Zoosper\Admin\Dashboard\DashboardWidgetPersonaliser;
use Zoosper\AdminDashboard\DashboardWidget;

function personalisationWidgets(): array
{
    return [
        new DashboardWidget('one', 'One', '1', 'First'),
        new DashboardWidget('two', 'Two', '2', 'Second'),
        new DashboardWidget('new', 'New', '3', 'Newly permitted'),
    ];
}

it('uses module defaults when no per-user preference exists', function (): void {
    $result = (new DashboardWidgetPersonaliser())->apply(new DashboardWidgetCollection(personalisationWidgets(), 2), null);

    expect(array_column($result->visibleWidgets, 'code'))->toBe(['one', 'two', 'new'])
        ->and($result->failureCount)->toBe(2)
        ->and($result->customised)->toBeFalse();
});

it('applies stored order and visibility after permission filtering and appends new widgets', function (): void {
    $result = (new DashboardWidgetPersonaliser())->apply(
        new DashboardWidgetCollection(personalisationWidgets()),
        new DashboardPreference(['two', 'retired'], ['two', 'one', 'retired']),
    );

    expect(array_column($result->availableWidgets, 'code'))->toBe(['two', 'one', 'new'])
        ->and(array_column($result->visibleWidgets, 'code'))->toBe(['one', 'new'])
        ->and($result->hiddenWidgetCodes)->toBe(['two']);
});

it('normalises a complete permitted submission into hidden and ordered state', function (): void {
    $preference = (new DashboardWidgetPersonaliser())->preferenceFromSubmission(
        personalisationWidgets(),
        ['one', 'two'],
        ['two'],
        ['two', 'one'],
    );

    expect($preference->hiddenWidgetCodes)->toBe(['one'])
        ->and($preference->widgetOrder)->toBe(['two', 'one']);
});

it('rejects unknown duplicate and inconsistent submitted widget codes', function (array $known, array $visible, array $order): void {
    expect(fn () => (new DashboardWidgetPersonaliser())->preferenceFromSubmission(
        personalisationWidgets(),
        $known,
        $visible,
        $order,
    ))->toThrow(InvalidArgumentException::class, 'Dashboard preference submission is invalid.');
})->with([
    'unknown code' => [['one', 'secret'], ['one'], ['one', 'secret']],
    'duplicate code' => [['one', 'one'], ['one'], ['one', 'one']],
    'visible outside known page state' => [['one'], ['two'], ['one']],
    'incomplete order' => [['one', 'two'], ['one'], ['one']],
]);
