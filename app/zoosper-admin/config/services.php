<?php

declare(strict_types=1);

/**
 * Admin module service registrations.
 *
 * Phase 1.25: registers the shared entity save event dispatcher and the
 * EntitySaveLifecycleRunner, and attaches the real PageSaveValidationListener so
 * page saves are validated through the entity save lifecycle.
 */

use Zoosper\Admin\Asset\AdminAssetRegistry;
use Zoosper\Admin\Asset\AdminAssetTemplateRenderer;
use Zoosper\Admin\Asset\AdminAssetViewDataProvider;
use Zoosper\Admin\Asset\AssetPathResolver;
use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Editor\ContentEditorInterface;
use Zoosper\Admin\Editor\ContentEditorRegistry;
use Zoosper\Admin\Editor\EditorJsContentEditor;
use Zoosper\Admin\Editor\TextareaContentEditor;
use Zoosper\Admin\Form\AdminFormUiConfigLoader;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Message\FlashMessageRenderer;
use Zoosper\Admin\Message\FlashMessageStoreInterface;
use Zoosper\Admin\Message\SessionFlashMessageStore;
use Zoosper\Admin\Navigation\AdminMenu;
use Zoosper\Admin\Navigation\AdminMenuLoader;
use Zoosper\Admin\UI\AdminComponentRenderer;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Container\ServiceContainer;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcher;
use Zoosper\Core\Entity\Save\EntitySaveEventDispatcherInterface;
use Zoosper\Core\Entity\Save\EntitySaveLifecycle;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Page\Save\PageSaveValidationListener;

return [
    LoginHistoryRepository::class => static fn (ServiceContainer $services): LoginHistoryRepository => new LoginHistoryRepository($services->get(PDO::class)),
    AuditLogRepository::class => static fn (ServiceContainer $services): AuditLogRepository => new AuditLogRepository($services->get(PDO::class)),
    AuditLogger::class => static fn (ServiceContainer $services): AuditLogger => new AuditLogger($services->get(AuditLogRepository::class)),
    AdminFormUiConfigLoader::class => static fn (ServiceContainer $services): AdminFormUiConfigLoader => new AdminFormUiConfigLoader($services->get(ModuleRegistry::class)),
    AdminAssetRegistry::class => static fn (ServiceContainer $services): AdminAssetRegistry => new AdminAssetRegistry($services->get(ModuleRegistry::class)),
    AdminAssetViewDataProvider::class => static fn (ServiceContainer $services): AdminAssetViewDataProvider => new AdminAssetViewDataProvider($services->get(AdminAssetRegistry::class)),
    AdminAssetTemplateRenderer::class => static fn (ServiceContainer $services): AdminAssetTemplateRenderer => new AdminAssetTemplateRenderer($services->get(AdminAssetRegistry::class)),
    AssetPathResolver::class => static fn (ServiceContainer $services): AssetPathResolver => new AssetPathResolver($services->get(ConfigRepository::class)),
    FlashMessageStoreInterface::class => static fn (ServiceContainer $services): FlashMessageStoreInterface => new SessionFlashMessageStore(),
    FlashMessageRenderer::class => static fn (ServiceContainer $services): FlashMessageRenderer => new FlashMessageRenderer(),
    TextareaContentEditor::class => static fn (ServiceContainer $services): TextareaContentEditor => new TextareaContentEditor(),
    EditorJsContentEditor::class => static fn (ServiceContainer $services): EditorJsContentEditor => new EditorJsContentEditor($services->get(TextareaContentEditor::class)),
    ContentEditorRegistry::class => static fn (ServiceContainer $services): ContentEditorRegistry => new ContentEditorRegistry(
        $services->get(EditorJsContentEditor::class),
        $services->get(TextareaContentEditor::class),
    ),
    ContentEditorInterface::class => static function (ServiceContainer $services): ContentEditorInterface {
        $config = $services->get(ConfigRepository::class)->array('editor');
        $preferred = (string) ($config['default_editor'] ?? 'editorjs');
        $fallback = (string) ($config['fallback_editor'] ?? 'textarea');

        return $services->get(ContentEditorRegistry::class)->preferred($preferred, $fallback);
    },
    AdminMenu::class => static fn (ServiceContainer $services): AdminMenu => new AdminMenu(new AdminMenuLoader($services->get(ModuleRegistry::class))),
    AdminLayout::class => static fn (ServiceContainer $services): AdminLayout => new AdminLayout(
        $services->get(AdminMenu::class),
        $services->get(ConfigRepository::class),
        $services->get('theme.admin_template_renderer'),
        $services->get(AdminAssetTemplateRenderer::class),
        $services->get(AdminAssetViewDataProvider::class),
        $services->get(FlashMessageStoreInterface::class),
        $services->get(FlashMessageRenderer::class),
    ),
    AdminViewRenderer::class => static fn (ServiceContainer $services): AdminViewRenderer => new AdminViewRenderer(
        $services->get('theme.admin_template_renderer'),
        $services->get(AdminLayout::class),
    ),
    AdminComponentRenderer::class => static fn (ServiceContainer $services): AdminComponentRenderer => new AdminComponentRenderer($services->get('theme.admin_template_renderer')),

    /*
     * Entity save lifecycle (Phase 1.25).
     *
     * The dispatcher is resolved once and cached by the container, so all callers
     * share a single instance with the same attached listeners.
     */
    EntitySaveEventDispatcherInterface::class => static function (): EntitySaveEventDispatcherInterface {
        // Shared, singleton dispatcher (the container caches the first result).
        // Module save listeners are attached here. NOTE (roadmap 1.26): replace this
        // hard-coded attach with module-contributed listener discovery so modules
        // register listeners without editing this central factory.
        $dispatcher = new EntitySaveEventDispatcher();
        $dispatcher->listen(EntitySaveLifecycle::VALIDATE_AFTER, new PageSaveValidationListener());

        return $dispatcher;
    },
    EntitySaveLifecycleRunner::class => static fn (ServiceContainer $services): EntitySaveLifecycleRunner => new EntitySaveLifecycleRunner($services->get(EntitySaveEventDispatcherInterface::class)),
];
