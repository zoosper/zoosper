<?php

declare(strict_types=1);

/**
 * Admin module service registrations.
 *
 * Phase 1.28: the shared entity save event dispatcher now DISCOVERS its listeners
 * from each module's config/entity_save_listeners.php via ModuleEntitySaveListenerLoader,
 * instead of hard-coding them here. Modules extend the save lifecycle without
 * editing this core file.
 */

use Zoosper\Admin\Asset\AdminAssetRegistry;
use Zoosper\Admin\Asset\AdminAssetTemplateRenderer;
use Zoosper\Admin\Asset\AdminAssetViewDataProvider;
use Zoosper\Admin\Asset\AssetPathResolver;
use Zoosper\Audit\AuditLogger;
use Zoosper\Audit\AuditLogRepository;
use Zoosper\Audit\LoginHistoryRepository;
use Zoosper\Audit\Admin\Grid\OperationalGridPageBuilder;
use Zoosper\Audit\Admin\Grid\OperationalGridPageBuilderFactory;
use Zoosper\Admin\Console\PruneLogsCommand;
use Zoosper\AdminGrid\GridViewStateResolver;
use Zoosper\Core\Editor\ContentEditorInterface;
use Zoosper\Admin\Editor\ContentEditorRegistry;
use Zoosper\Admin\Editor\Config\ContentEditorRuntimeConfig;
use Zoosper\Admin\Editor\Config\ContentEditorRuntimeConfigFactory;
use Zoosper\Admin\Editor\EditorJsContentEditor;
use Zoosper\Admin\Editor\TextareaContentEditor;
use Zoosper\AdminForm\AdminFormRegistry;
use Zoosper\AdminForm\AdminFormRenderer;
use Zoosper\AdminForm\AdminFormUiConfigLoader;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Admin\Message\FlashMessageRenderer;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Admin\Message\SessionFlashMessageStore;
use Zoosper\Admin\Dashboard\DashboardPersonalisationService;
use Zoosper\Admin\Dashboard\DashboardPreferenceRepository;
use Zoosper\Admin\Dashboard\DashboardWidgetPersonaliser;
use Zoosper\Admin\Dashboard\ModuleDashboardWidgetLoader;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\Admin\Navigation\AdminNavigationRenderer;
use Zoosper\Admin\Navigation\AdminSectionMetadataLoader;
use Zoosper\Admin\Navigation\AdminSectionBuilder;
use Zoosper\Admin\Theme\ModuleAdminColourThemeLoader;
use Zoosper\Admin\UI\AdminComponentRenderer;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\AdminDashboard\Contract\DashboardRolePreferenceRepositoryInterface;
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Announcement\AdminAnnouncementProviderInterface;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Audit\Contract\LoginHistoryRecorderInterface;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\ScopedConfig\ScopeConfigRepository;
use Zoosper\ScopedConfig\ScopeContext;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcher;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcherInterface;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Entity\Save\ModuleEntitySaveListenerLoader;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Url\AdminPathCollectionTransformer;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Media\EditorJs\EditorJsImageToolConfig;

return [
    OperationalGridPageBuilderFactory::class => static fn(ServiceContainer $services): OperationalGridPageBuilderFactory => new OperationalGridPageBuilderFactory($services->get(GridViewStateResolver::class)),
    OperationalGridPageBuilder::class => static fn(ServiceContainer $services): OperationalGridPageBuilder => $services->get(OperationalGridPageBuilderFactory::class)->create(),
    PruneLogsCommand::class => static fn(ServiceContainer $services): PruneLogsCommand => new PruneLogsCommand(
        $services->get(AuditLogRepository::class),
        $services->get(LoginHistoryRepository::class),
    ),
    LoginHistoryRepository::class => static fn(ServiceContainer $services): LoginHistoryRepository => new LoginHistoryRepository($services->get(PDO::class)),
    AuditLogRepository::class => static fn(ServiceContainer $services): AuditLogRepository => new AuditLogRepository($services->get(PDO::class)),
    AuditLogger::class => static fn(ServiceContainer $services): AuditLogger => new AuditLogger($services->get(AuditLogRepository::class)),
    AdminFormRegistry::class => static function (ServiceContainer $services): AdminFormRegistry {
        $registry = new AdminFormRegistry();
        if ($services->has(AdminFormUiConfigLoader::class)) {
            $services->get(AdminFormUiConfigLoader::class)->registerAll($registry);
        }

        return $registry;
    },
    AdminFormRenderer::class => static fn(ServiceContainer $services): AdminFormRenderer => new AdminFormRenderer(),
    AdminFormUiConfigLoader::class => static fn(ServiceContainer $services): AdminFormUiConfigLoader => new AdminFormUiConfigLoader($services->get(ModuleRegistry::class)),
    ModuleDashboardWidgetLoader::class => static fn(ServiceContainer $services): ModuleDashboardWidgetLoader => new ModuleDashboardWidgetLoader($services->get(ModuleRegistry::class), $services),
    DashboardPreferenceRepository::class => static fn(ServiceContainer $services): DashboardPreferenceRepository => new DashboardPreferenceRepository($services->get(PDO::class)),
    DashboardWidgetPersonaliser::class => static fn(ServiceContainer $services): DashboardWidgetPersonaliser => new DashboardWidgetPersonaliser(),
    DashboardPersonalisationService::class => static fn(ServiceContainer $services): DashboardPersonalisationService => new DashboardPersonalisationService($services->get(ModuleDashboardWidgetLoader::class), $services->get(DashboardPreferenceRepository::class), $services->get(DashboardWidgetPersonaliser::class), $services->has(DashboardRolePreferenceRepositoryInterface::class) ? $services->get(DashboardRolePreferenceRepositoryInterface::class) : null),
    AdminAssetRegistry::class => static fn(ServiceContainer $services): AdminAssetRegistry => new AdminAssetRegistry($services->get(ModuleRegistry::class)),
    ModuleAdminColourThemeLoader::class => static fn(ServiceContainer $services): ModuleAdminColourThemeLoader => new ModuleAdminColourThemeLoader($services->get(ModuleRegistry::class)),
    AdminAssetViewDataProvider::class => static fn(ServiceContainer $services): AdminAssetViewDataProvider => new AdminAssetViewDataProvider($services->get(AdminAssetRegistry::class)),
    AdminAssetTemplateRenderer::class => static fn(ServiceContainer $services): AdminAssetTemplateRenderer => new AdminAssetTemplateRenderer($services->get(AdminAssetRegistry::class)),
    AssetPathResolver::class => static fn(ServiceContainer $services): AssetPathResolver => new AssetPathResolver($services->get(ConfigRepository::class)),
    FlashMessageStoreInterface::class => static fn(ServiceContainer $services): FlashMessageStoreInterface => new SessionFlashMessageStore(),
    FlashMessageRenderer::class => static fn(ServiceContainer $services): FlashMessageRenderer => new FlashMessageRenderer(),
    AdminSectionMetadataLoader::class => static fn(ServiceContainer $services): AdminSectionMetadataLoader => new AdminSectionMetadataLoader($services->get(ModuleRegistry::class)),
    AdminSectionBuilder::class => static fn(ServiceContainer $services): AdminSectionBuilder => new AdminSectionBuilder($services->get(AdminSectionMetadataLoader::class)),
    AdminMenu::class => static fn(ServiceContainer $services): AdminMenu => new AdminMenu(
        new AdminMenuLoader(
            $services->get(ModuleRegistry::class),
            $services->get(AdminPathCollectionTransformer::class),
        ),
        $services->get(AdminSectionBuilder::class),
    ),
    AdminNavigationRenderer::class => static fn(ServiceContainer $services): AdminNavigationRenderer => new AdminNavigationRenderer(),
    AdminLayout::class => static fn(ServiceContainer $services): AdminLayout => new AdminLayout(
        $services->get(AdminMenu::class),
        $services->get(AdminNavigationRenderer::class),
        $services->get(ConfigRepository::class),
        $services->get('theme.admin_template_renderer'),
        $services->get(AdminAssetTemplateRenderer::class),
        $services->get(AdminAssetViewDataProvider::class),
        $services->get(FlashMessageStoreInterface::class),
        $services->get(FlashMessageRenderer::class),
        $services->get(CsrfTokenManager::class),
        $services->get(AdminUrlGenerator::class),
        $services->get(ModuleAdminColourThemeLoader::class),
        $services->has(AdminAnnouncementProviderInterface::class) ? $services->get(AdminAnnouncementProviderInterface::class) : null,
    ),
    AdminViewRenderer::class => static fn(ServiceContainer $services): AdminViewRenderer => new AdminViewRenderer(
        $services->get('theme.admin_template_renderer'),
        $services->get(AdminLayout::class),
    ),
    AdminComponentRenderer::class => static fn(ServiceContainer $services): AdminComponentRenderer => new AdminComponentRenderer($services->get('theme.admin_template_renderer')),
    EntitySaveEventDispatcherInterface::class => static function (ServiceContainer $services): EntitySaveEventDispatcherInterface {
        $dispatcher = new EntitySaveEventDispatcher();
        (new ModuleEntitySaveListenerLoader($services->get(ModuleRegistry::class), $services))->attach($dispatcher);

        return $dispatcher;
    },
    EntitySaveLifecycleRunner::class => static fn(ServiceContainer $services): EntitySaveLifecycleRunner => new EntitySaveLifecycleRunner($services->get(EntitySaveEventDispatcherInterface::class)),
// Phase 1.41: bind Core interfaces to the real Admin implementations, so
// feature modules (two-factor, and later media/page) can depend on the
// interface instead of these concrete Admin classes directly.
    AuditLoggerInterface::class => static fn(ServiceContainer $services): AuditLoggerInterface => $services->get(AuditLogger::class),
    LoginHistoryRecorderInterface::class => static fn(ServiceContainer $services): LoginHistoryRecorderInterface => $services->get(LoginHistoryRepository::class),
    AdminLayoutRendererInterface::class => static fn(ServiceContainer $services): AdminLayoutRendererInterface => $services->get(AdminLayout::class),
    AdminViewRendererInterface::class => static fn(ServiceContainer $services): AdminViewRendererInterface => $services->get(AdminViewRenderer::class),
];











