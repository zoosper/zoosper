<?php

declare(strict_types=1);

namespace Zoosper\Admin\Dashboard;

use InvalidArgumentException;
use Zoosper\AdminDashboard\DashboardWidget;

final readonly class DashboardWidgetPersonaliser
{
    public function apply(
        DashboardWidgetCollection $collection,
        ?DashboardPreference $preference,
    ): PersonalisedDashboardWidgetCollection {
        $widgetsByCode = [];
        foreach ($collection->widgets as $widget) {
            $widgetsByCode[$widget->code] = $widget;
        }

        if ($preference === null) {
            return new PersonalisedDashboardWidgetCollection(
                $collection->widgets,
                $collection->widgets,
                [],
                $collection->failureCount,
            );
        }

        $ordered = [];
        foreach ($preference->widgetOrder as $code) {
            if (isset($widgetsByCode[$code]) && !isset($ordered[$code])) {
                $ordered[$code] = $widgetsByCode[$code];
            }
        }
        foreach ($widgetsByCode as $code => $widget) {
            $ordered[$code] ??= $widget;
        }

        $hidden = array_fill_keys(array_intersect($preference->hiddenWidgetCodes, array_keys($widgetsByCode)), true);
        $visible = array_values(array_filter(
            $ordered,
            static fn (DashboardWidget $widget): bool => !isset($hidden[$widget->code]),
        ));

        return new PersonalisedDashboardWidgetCollection(
            array_values($ordered),
            $visible,
            array_keys($hidden),
            $collection->failureCount,
            true,
        );
    }

    /**
     * @param list<DashboardWidget> $availableWidgets Permission-filtered widgets only.
     */
    public function preferenceFromSubmission(
        array $availableWidgets,
        mixed $knownWidgetCodes,
        mixed $visibleWidgetCodes,
        mixed $widgetOrder,
    ): DashboardPreference {
        $allowed = array_column($availableWidgets, 'code');
        $known = $this->validatedList($knownWidgetCodes, $allowed, 'known widgets');
        $visible = $this->validatedList($visibleWidgetCodes ?? [], $allowed, 'visible widgets');
        $order = $this->validatedList($widgetOrder, $allowed, 'widget order');

        if (array_diff($visible, $known) !== []) {
            throw new InvalidArgumentException('Dashboard preference submission is invalid.');
        }
        if (count($order) !== count($known) || array_diff($order, $known) !== [] || array_diff($known, $order) !== []) {
            throw new InvalidArgumentException('Dashboard preference submission is invalid.');
        }

        return new DashboardPreference(
            array_values(array_diff($known, $visible)),
            $order,
        );
    }

    /**
     * @param list<string> $allowed
     * @return list<string>
     */
    private function validatedList(mixed $submitted, array $allowed, string $field): array
    {
        if (!is_array($submitted) || !array_is_list($submitted)) {
            throw new InvalidArgumentException('Dashboard ' . $field . ' must be a list.');
        }

        $values = [];
        foreach ($submitted as $value) {
            if (!is_string($value) || !in_array($value, $allowed, true) || isset($values[$value])) {
                throw new InvalidArgumentException('Dashboard preference submission is invalid.');
            }
            $values[$value] = true;
        }

        return array_keys($values);
    }
}
