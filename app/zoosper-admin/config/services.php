<?php

declare(strict_types=1);

use Zoosper\Admin\Asset\AdminAssetRegistry;
use Zoosper\Admin\Asset\AdminAssetTemplateRenderer;
use Zoosper\Admin\Asset\AdminAssetViewDataProvider;
use Zoosper\Admin\Asset\AssetPathResolver;
use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Form\AdminFormUiConfigLoader;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\Admin\UI\AdminComponentRenderer;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Theme\Template\TemplateRenderer;

return [
    LoginHistoryRepository::class => static fn (ServiceContainer $services): LoginHistoryRepository => new LoginHistoryRepository($services->get(PDO::class)),
    AuditLogRepository::class => static fn (ServiceContainer $services): AuditLogRepository => new AuditLogRepository($services->get(PDO::class)),
    AuditLogger::class => static fn (ServiceContainer $services): AuditLogger => new AuditLogger($services->get(AuditLogRepository::class)),
    AdminFormUiConfigLoader::class => static fn (ServiceContainer $services): AdminFormUiConfigLoader => new AdminFormUiConfigLoader($services->get(ModuleRegistry::class)),
    AdminAssetRegistry::class => static fn (ServiceContainer $services): AdminAssetRegistry => new AdminAssetRegistry($services->get(ModuleRegistry::class)),
    AdminAssetViewDataProvider::class => static fn (ServiceContainer $services): AdminAssetViewDataProvider => new AdminAssetViewDataProvider($services->get(AdminAssetRegistry::class)),
    AdminAssetTemplateRenderer::class => static fn (ServiceContainer $services): AdminAssetTemplateRenderer => new AdminAssetTemplateRenderer($services->get(AdminAssetRegistry::class)),
    AssetPathResolver::class => static fn (ServiceContainer $services): AssetPathResolver => new AssetPathResolver($services->get(ConfigRepository::class)),
    AdminMenu::class => static fn (ServiceContainer $services): AdminMenu => new AdminMenu(new AdminMenuLoader($services->get(ModuleRegistry::class))),
    AdminLayout::class => static fn (ServiceContainer $services): AdminLayout => new AdminLayout(
        $services->get(AdminMenu::class),
        $services->get(ConfigRepository::class),
        $services->get('theme.admin_template_renderer'),
        $services->get(AdminAssetTemplateRenderer::class),
        $services->get(AdminAssetViewDataProvider::class),
    ),
    AdminViewRenderer::class => static fn (ServiceContainer $services): AdminViewRenderer => new AdminViewRenderer(
        $services->get('theme.admin_template_renderer'),
        $services->get(AdminLayout::class),
    ),
    AdminComponentRenderer::class => static fn (ServiceContainer $services): AdminComponentRenderer => new AdminComponentRenderer($services->get('theme.admin_template_renderer')),
];
