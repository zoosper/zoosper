<?php

declare(strict_types=1);

namespace Zoosper\Admin\Dashboard;

use Throwable;
use Zoosper\AdminDashboard\Contract\DashboardWidgetContributorInterface;
use Zoosper\AdminDashboard\DashboardWidget;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;

final readonly class ModuleDashboardWidgetLoader
{
    public function __construct(private ModuleRegistry $modules, private ServiceContainer $services)
    {
    }

    public function forUser(AdminUser $user): DashboardWidgetCollection
    {
        $widgets = [];
        $failures = 0;

        foreach ($this->modules->enabledModules() as $module) {
            $file = $module->configPath('admin_dashboard.php');
            if (!is_file($file)) {
                continue;
            }

            try {
                $config = require $file;
            } catch (Throwable) {
                ++$failures;
                continue;
            }
            if (!is_array($config)) {
                ++$failures;
                continue;
            }

            foreach ($config as $declaration) {
                if (!is_array($declaration)) {
                    ++$failures;
                    continue;
                }

                $serviceId = $declaration['service'] ?? null;
                $permission = $declaration['permission'] ?? null;
                if (!is_string($serviceId) || $serviceId === '' || !is_string($permission) || $permission === '') {
                    ++$failures;
                    continue;
                }

                if (!$user->can($permission)) {
                    continue;
                }

                try {
                    $contributor = $this->services->get($serviceId);
                    if (!$contributor instanceof DashboardWidgetContributorInterface) {
                        ++$failures;
                        continue;
                    }

                    $candidateWidgets = [];
                    foreach ($contributor->widgets() as $widget) {
                        if (!$widget instanceof DashboardWidget || isset($widgets[$widget->code]) || isset($candidateWidgets[$widget->code])) {
                            throw new \RuntimeException('Dashboard contributors must yield uniquely coded DashboardWidget values.');
                        }
                        $candidateWidgets[$widget->code] = $widget;
                    }
                    $widgets += $candidateWidgets;
                } catch (Throwable) {
                    ++$failures;
                }
            }
        }

        $widgets = array_values($widgets);
        usort($widgets, static fn (DashboardWidget $a, DashboardWidget $b): int => [$a->sortOrder, $a->title, $a->code] <=> [$b->sortOrder, $b->title, $b->code]);

        return new DashboardWidgetCollection($widgets, $failures);
    }
}










