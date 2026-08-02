<?php

declare(strict_types=1);

function update(string $path, callable $transform): void
{
    $source = file_get_contents($path);
    if ($source === false) throw new RuntimeException('Unable to read ' . $path);
    $updated = $transform($source);
    if ($updated === $source) return;
    if (file_put_contents($path, $updated) === false) throw new RuntimeException('Unable to write ' . $path);
}
function addUse(string $source, string $use, string $anchor): string
{
    if (str_contains($source, $use)) return $source;
    if (!str_contains($source, $anchor)) throw new RuntimeException('Missing import anchor: ' . $anchor);
    return str_replace($anchor, $anchor . "\n" . $use, $source);
}

update('packages/zoosper-store-orders/config/admin_routes.php', static function (string $s): string {
    if (str_contains($s, "'/admin/store-orders/export'")) return $s;
    return str_replace("return [\n", "return [\n    ['method' => 'GET', 'path' => '/admin/store-orders/export', 'controller' => StoreOrderCsvExportController::class, 'action' => 'export', 'permission' => 'store_order.export'],\n", addUse($s, 'use Zoosper\\StoreOrders\\Admin\\StoreOrderCsvExportController;', 'use Zoosper\\StoreOrders\\Admin\\StoreOrderAdminController;'));
});

update('packages/zoosper-store-orders/config/controllers.php', static function (string $s): string {
    $s = addUse($s, 'use Zoosper\\AdminGrid\\GridWorkspaceCsvExportService;', 'use Zoosper\\AdminGrid\\GridWorkspaceMutationFormsRenderer;');
    $s = addUse($s, 'use Zoosper\\StoreOrders\\Admin\\StoreOrderCsvExportController;', 'use Zoosper\\StoreOrders\\Admin\\StoreOrderAdminController;');
    if (str_contains($s, 'StoreOrderCsvExportController::class =>')) return $s;
    $entry = <<<'PHP'
    StoreOrderCsvExportController::class => static fn (ServiceContainer $services): StoreOrderCsvExportController => new StoreOrderCsvExportController(
        guard: $services->get(SessionGuard::class),
        workspace: $services->get(StoreOrderGridWorkspace::class),
        dataSources: new StoreOrderDataSourceFactory(),
        exports: $services->get(GridWorkspaceCsvExportService::class),
        config: $services->get(ConfigRepository::class)->array('store_orders'),
    ),
PHP;
    return str_replace("return [\n", "return [\n" . $entry, $s);
});

update('packages/zoosper-store-orders/src/Admin/StoreOrderGridWorkspace.php', static fn (string $s): string => str_replace("self::ACTION . '?grid_export=current'", "self::ACTION . '/export'", $s));

update('app/zoosper-page/config/admin_routes.php', static function (string $s): string {
    if (str_contains($s, "'/admin/pages/export'")) return $s;
    $entry = "  array (\n    'method' => 'GET',\n    'path' => '/admin/pages/export',\n    'controller' => 'Zoosper\\\\Page\\\\Admin\\\\Controller\\\\PageCsvExportController',\n    'action' => 'export',\n    'permission' => 'page.manage',\n  ),\n";
    return str_replace("return array (\n", "return array (\n" . $entry, $s);
});

update('app/zoosper-page/config/controllers.php', static function (string $s): string {
    $imports = [
        ['use Zoosper\\AdminGrid\\GridCompactWorkspaceRenderer;', 'use Zoosper\\AdminGrid\\GridWorkspaceAuditedCsvExportService;'],
        ['use Zoosper\\AdminGrid\\GridWorkspaceAuditedCsvExportService;', 'use Zoosper\\Page\\Admin\\Controller\\PageCsvExportController;'],
        ['use Zoosper\\Page\\Admin\\Controller\\PageCsvExportController;', 'use Zoosper\\Page\\Admin\\PageGridAuditedExportCoordinator;'],
        ['use Zoosper\\Page\\Admin\\PageGridAuditedExportCoordinator;', 'use Zoosper\\Page\\Admin\\PageGridExportDataSource;'],
        ['use Zoosper\\Page\\Admin\\PageGridExportDataSource;', 'use Zoosper\\Page\\Admin\\PageGridExportRequestCoordinator;'],
        ['use Zoosper\\Page\\Admin\\PageGridExportRequestCoordinator;', 'use Zoosper\\Page\\Admin\\PageGridExportSqlBuilder;'],
        ['use Zoosper\\Page\\Admin\\PageGridExportSqlBuilder;', 'use Zoosper\\Page\\Admin\\PageGridHttpCoordinator;'],
        ['use Zoosper\\Page\\Admin\\PageGridHttpCoordinator;', 'use Zoosper\\Page\\Admin\\PageGridMutationHandler;'],
        ['use Zoosper\\Page\\Admin\\PageGridMutationHandler;', 'use Zoosper\\Page\\Admin\\PdoPageGridExportRepository;'],
        ['use Zoosper\\Page\\Admin\\PdoPageGridExportRepository;', 'use Zoosper\\AdminGrid\\GridWorkspaceMutationGuard;'],
    ];
    foreach ($imports as [$anchor, $use]) $s = addUse($s, $use, $anchor);
    if (str_contains($s, 'PageCsvExportController::class =>')) return $s;
    $entry = <<<'PHP'
    PageCsvExportController::class => static function (ServiceContainer $services): PageCsvExportController {
        $definition = new PageGridDefinition(
            $services->has(GridColumnRegistry::class) ? $services->get(GridColumnRegistry::class) : null,
            new PageGridSiteFilter(new PageSiteFilterOptions($services->get(SiteRepository::class))),
        );
        $workspace = new PageGridWorkspace(
            $definition,
            $services->get(GridViewStateResolver::class),
            new GridCompactWorkspaceRenderer(),
        );
        $http = new PageGridHttpCoordinator(
            $workspace,
            new PageGridMutationHandler($services->get(\Zoosper\AdminGrid\GridViewMutationService::class), $definition),
            $services->get(GridWorkspaceMutationGuard::class),
        );
        $repository = new PdoPageGridExportRepository($services->get(\PDO::class), new PageGridExportSqlBuilder());
        $requestExports = new PageGridExportRequestCoordinator(
            $http,
            new PageGridExportDataSource($repository),
            new PageGridAuditedExportCoordinator($services->get(GridWorkspaceAuditedCsvExportService::class)),
        );
        return new PageCsvExportController($services->get(SessionGuard::class), $requestExports);
    },
PHP;
    return str_replace("return [\n", "return [\n" . $entry, $s);
});
