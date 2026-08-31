<?php

declare(strict_types=1);

use Zoosper\Admin\Dashboard\ModuleDashboardWidgetLoader;
use Zoosper\AdminDashboard\Contract\DashboardWidgetContributorInterface;
use Zoosper\AdminDashboard\DashboardWidget;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;

function dashboardTestUser(array $permissions): AdminUser
{
    return new AdminUser(1, 'admin@example.test', 'Admin', 'hash', 'active', $permissions);
}

function dashboardModuleRoot(string $config): string
{
    $root = sys_get_temp_dir() . '/zoosper-dashboard-' . bin2hex(random_bytes(6));
    mkdir($root . '/app/example/config', 0777, true);
    file_put_contents($root . '/app/example/module.php', "<?php return ['name' => 'example', 'enabled' => true];");
    file_put_contents($root . '/app/example/config/admin_dashboard.php', $config);
    return $root;
}

it('checks permission before resolving and executing a module contributor', function (): void {
    $root = dashboardModuleRoot("<?php return [['service' => 'example.widget', 'permission' => 'reports.view']];");
    $services = new ServiceContainer();
    $executions = 0;
    $services->factory('example.widget', static function () use (&$executions): DashboardWidgetContributorInterface {
        ++$executions;
        return new class implements DashboardWidgetContributorInterface {
            public function widgets(): iterable { yield new DashboardWidget('example.total', 'Total', '7', 'Current total'); }
        };
    });

    $denied = (new ModuleDashboardWidgetLoader(new ModuleRegistry($root), $services))->forUser(dashboardTestUser([]));
    expect($denied->widgets)->toBe([])->and($executions)->toBe(0);

    $allowed = (new ModuleDashboardWidgetLoader(new ModuleRegistry($root), $services))->forUser(dashboardTestUser(['reports.view']));
    expect($allowed->widgets)->toHaveCount(1)->and($allowed->widgets[0]->code)->toBe('example.total')->and($executions)->toBe(1);
});

it('isolates failed contributors and keeps deterministic healthy widgets', function (): void {
    $root = dashboardModuleRoot("<?php return [
['service' => 'broken.widget', 'permission' => 'reports.view'],
['service' => 'healthy.widget', 'permission' => 'reports.view'],
];");
    $services = new ServiceContainer();
    $services->set('broken.widget', new class implements DashboardWidgetContributorInterface {
        public function widgets(): iterable { throw new RuntimeException('private failure'); yield; }
    });
    $services->set('healthy.widget', new class implements DashboardWidgetContributorInterface {
        public function widgets(): iterable {
            yield new DashboardWidget('example.second', 'Second', '2', 'Second widget', 20);
            yield new DashboardWidget('example.first', 'First', '1', 'First widget', 10);
        }
    });

    $result = (new ModuleDashboardWidgetLoader(new ModuleRegistry($root), $services))->forUser(dashboardTestUser(['reports.view']));
    expect(array_map(static fn (DashboardWidget $widget): string => $widget->code, $result->widgets))
        ->toBe(['example.first', 'example.second'])
        ->and($result->failureCount)->toBe(1);
});

it('rejects malformed declarations and duplicate widget codes without breaking healthy output', function (): void {
    $root = dashboardModuleRoot("<?php return [42, ['service' => 'duplicate.widget', 'permission' => 'reports.view']];");
    $services = new ServiceContainer();
    $services->set('duplicate.widget', new class implements DashboardWidgetContributorInterface {
        public function widgets(): iterable {
            yield new DashboardWidget('example.duplicate', 'First', '1', 'First');
            yield new DashboardWidget('example.duplicate', 'Second', '2', 'Second');
        }
    });

    $result = (new ModuleDashboardWidgetLoader(new ModuleRegistry($root), $services))->forUser(dashboardTestUser(['reports.view']));
    expect($result->widgets)->toBe([])->and($result->failureCount)->toBe(2);
});










