<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Editor\ContentEditorInterface;
use Zoosper\Admin\Form\AdminFormConfigAggregator;
use Zoosper\Admin\Form\AdminFormConfigProviderFactory;
use Zoosper\Admin\Form\AdminFormProcessorConfigFactory;
use Zoosper\Admin\Form\AdminFormProcessorRegistry;
use Zoosper\Admin\Form\AdminFormProviderRegistry;
use Zoosper\Admin\Form\AdminFormRenderer;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\Message\FlashMessageStoreInterface;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Access\Permission;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Config\ConfigRepository;
use Zoosper\Core\Entity\Save\EntityDataObject;
use Zoosper\Core\Entity\Save\EntitySaveContext;
use Zoosper\Core\Entity\Save\EntitySaveLifecycleRunner;
use Zoosper\Core\Entity\Save\FieldDefinitionRegistry;
use Zoosper\Core\Event\EventDispatcherInterface;
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\I18n\AdminContextTranslatorResolver;
use Zoosper\Core\I18n\IdentityTranslator;
use Zoosper\Core\I18n\TranslatorInterface;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Page\Admin\Form\PageContentSectionProvider;
use Zoosper\Page\Admin\Form\PageDetailsSectionProvider;
use Zoosper\Page\Admin\Form\PagePublishingSectionProvider;
use Zoosper\Page\Admin\Form\PageSeoSectionProvider;
use Zoosper\Page\Admin\PageGridCriteria;
use Zoosper\Page\Admin\PageGridRepository;
use Zoosper\Page\Content\BlockJsonValidator;
use Zoosper\Page\Event\PageEvents;
use Zoosper\Page\Event\PagePublishedEvent;
use Zoosper\Page\Event\PageUnpublishedEvent;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Admin CRUD controller for CMS pages.
 *
 * The controller orchestrates request flow only. The page form UI is composed
 * through admin form section providers so core and third-party modules can add,
 * replace or reorder sections without modifying this controller.
 *
 * Phase 1.27: AdminViewRenderer is now required and the index list is rendered
 * by a Latte template (no controller heredoc). Caught exceptions (including
 * PDOExceptions, which extend RuntimeException) are logged via ErrorHandler
 * before returning the 422 form, so save failures are never silent.
 */
final readonly class PageAdminController
{
    public function __construct(
        private SessionGuard                     $guard,
        private CsrfTokenManager                 $csrf,
        private PageRepository                   $pages,
        private SiteRepository                   $sites,
        private PageRenderer                     $renderer,
        private AdminLayout                      $layout,
        private AdminViewRenderer                $views,
        private ?PageGridRepository              $pageGrid = null,
        private ?HtmlSanitizerInterface          $htmlSanitizer = null,
        private ?FlashMessageStoreInterface      $flashMessages = null,
        private ?ConfigRepository                $config = null,
        private ?ContentEditorInterface          $contentEditor = null,
        private ?TranslatorInterface             $translator = null,
        private ?AdminContextTranslatorResolver  $adminContextTranslatorResolver = null,
        private ?AdminFormProviderRegistry       $pageFormSections = null,
        private ?AdminFormRenderer               $adminFormRenderer = null,
        private ?AdminFormConfigProviderFactory  $adminFormConfigProviderFactory = null,
        private ?AdminFormProcessorRegistry      $pageFormProcessors = null,
        private ?AdminFormProcessorConfigFactory $adminFormProcessorConfigFactory = null,
        private ?EntitySaveLifecycleRunner       $saveLifecycle = null,
        private ?ErrorHandler                    $errorHandler = null,
        private ?EventDispatcherInterface        $events = null,
    )
    {
    }

    public function index(Request $request): Response
    {
        $user = $this->requirePageManager();
        if ($user === null) {
            return Response::redirect($this->adminUrl('/login'));
        }

        $criteria = PageGridCriteria::fromQuery($_GET);
        $pagination = $this->pageGrid?->paginate($criteria);
        $pages = $pagination?->items ?? $this->pages->all();
        $sites = $this->sites->allActive();

        return Response::html($this->views->render(
            'Pages',
            'zoosper-page::admin/pages/index',
            [
                'pages' => $pages,
                'pagination' => $pagination,
                'criteria' => $criteria,
                'sites' => $sites,
            ],
            $user,
            'pages',
        ));
    }

    private function requirePageManager(): ?AdminUser
    {
        return $this->guard->requirePermission(Permission::PageManage->value);
    }

    private function adminUrl(string $path): string
    {
        $adminConfig = $this->config?->array('admin') ?? [];
        $basePath = (string)($adminConfig['base_path'] ?? '/admin');

        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }

    private function html(string $title, string $content, int $statusCode = 200): Response
    {
        return Response::html($this->layout->render($title, $content, $this->guard->user(), 'pages'), $statusCode);
    }

    public function createForm(Request $request): Response
    {
        if ($this->requirePageManager() === null) {
            return Response::redirect($this->adminUrl('/login'));
        }

        return $this->html('Create page', $this->form($this->adminUrl('/pages/create')));
    }

    /** @param array<string, mixed> $submitted */
    private function form(string $action, ?Page $page = null, ?string $error = null, array $submitted = []): string
    {
        $token = $this->csrf->token();
        $siteId = (int)($submitted['site_id'] ?? $page?->siteId ?? 0);
        $content = $this->e((string)($submitted['content'] ?? $page?->content ?? ''));
        $contentJson = $this->e((string)($submitted['content_json'] ?? $page?->contentJson ?? ''));
        $editorHtml = $this->renderContentEditor($content, $page, $contentJson);
        $errorHtml = $error !== null ? '<p class="error">' . $this->e($error) . '</p>' : '';

        $context = [
            'page' => $page,
            'submitted' => $submitted,
            'siteOptions' => $this->siteOptions($siteId),
            'title' => $this->e((string)($submitted['title'] ?? $page?->title ?? '')),
            'slug' => $this->e((string)($submitted['slug'] ?? $page?->slug ?? '')),
            'editorHtml' => $editorHtml,
            'contentJson' => $contentJson,
            'metaTitle' => $this->e((string)($submitted['meta_title'] ?? $page?->metaTitle ?? '')),
            'metaDescription' => $this->e((string)($submitted['meta_description'] ?? $page?->metaDescription ?? '')),
            'metaKeywords' => $this->e((string)($submitted['meta_keywords'] ?? $page?->metaKeywords ?? '')),
            'canonicalUrl' => $this->e((string)($submitted['canonical_url'] ?? $page?->canonicalUrl ?? '')),
            'publishChecked' => (isset($submitted['publish']) || $page?->isPublished()) ? ' checked' : '',
            'backUrl' => $this->e($this->adminUrl('/pages')),
        ];

        $registry = $this->pageFormSections ?? $this->defaultPageFormSectionRegistry();
        $renderer = $this->adminFormRenderer ?? new AdminFormRenderer();
        $sections = $registry->sectionsFor('page.form', $context);

        return $errorHtml . $renderer->render($action, $token, $sections);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    private function renderContentEditor(string $escapedContent, ?Page $page = null, string $escapedContentJson = ''): string
    {
        $content = html_entity_decode($escapedContent, ENT_QUOTES, 'UTF-8');
        $contentJson = html_entity_decode($escapedContentJson, ENT_QUOTES, 'UTF-8');

        if ($this->contentEditor === null) {
            return '<input type="hidden" name="content_json" value="' . $escapedContentJson . '">'
                . '<textarea name="content" rows="14" required>' . $escapedContent . '</textarea>';
        }

        return $this->contentEditor->render('content', $content, [
            'label' => 'Content',
            'rows' => 14,
            'required' => true,
            'page' => $page,
            'content_json' => $contentJson,
        ]);
    }

    private function siteOptions(int $selectedSiteId): string
    {
        $html = '';
        foreach ($this->sites->allActive() as $site) {
            $selected = $site->id === $selectedSiteId ? ' selected' : '';
            $label = $this->e($site->name . ' (' . $site->code . ')');
            $html .= '<option value="' . $site->id . '"' . $selected . '>' . $label . '</option>';
        }

        return $html;
    }

    private function defaultPageFormSectionRegistry(): AdminFormProviderRegistry
    {
        $factory = $this->adminFormConfigProviderFactory ?? new AdminFormConfigProviderFactory();
        $rootConfig = $this->config?->array('admin_forms') ?? [];
        $moduleConfig = (new AdminFormConfigAggregator($this->projectRootPath()))->aggregate($rootConfig);

        return $factory->create($moduleConfig, [
            'page.form' => [
                PageDetailsSectionProvider::class,
                PageContentSectionProvider::class,
                PageSeoSectionProvider::class,
                PagePublishingSectionProvider::class,
            ],
        ]);
    }

    private function projectRootPath(): string
    {
        return dirname(__DIR__, 4);
    }

    public function create(Request $request): Response
    {
        $user = $this->requirePageManager();
        if ($user === null) {
            return Response::redirect($this->adminUrl('/login'));
        }

        $form = $request->form();

        $processorError = $this->processPageForm('create', $form, null, $user);
        if ($processorError !== null) {
            $this->flashMessages?->error($this->t('Unable to create page. Please review the form.'), 'page.processor_create_failed');

            return $this->html('Create page', $this->form($this->adminUrl('/pages/create'), error: $processorError, submitted: $form), 422);
        }

        try {
            $createdId = null;
            $context = $this->runEntitySave('page', $form, null, function (EntitySaveContext $c) use ($form, $user, &$createdId): void {
                $createdId = $this->pages->create(
                    siteId: (int)($form['site_id'] ?? 0),
                    title: trim((string)($form['title'] ?? '')),
                    slug: $this->normaliseSlug((string)($form['slug'] ?? '')),
                    content: $this->sanitiseContent((string)($form['content'] ?? '')),
                    status: isset($form['publish']) ? 'published' : 'draft',
                    userId: $user->id,
                    contentFormat: 'html',
                    contentJson: $this->normaliseContentJson($form['content_json'] ?? null),
                    metaTitle: $this->normaliseOptionalString($form['meta_title'] ?? null),
                    metaDescription: $this->normaliseOptionalString($form['meta_description'] ?? null),
                    metaKeywords: $this->normaliseOptionalString($form['meta_keywords'] ?? null),
                    canonicalUrl: $this->normaliseOptionalString($form['canonical_url'] ?? null),
                );
            });

            if ($context->hasErrors()) {
                $this->flashMessages?->error($this->t('Unable to create page. Please review the form.'), 'page.create_failed');

                return $this->html('Create page', $this->form($this->adminUrl('/pages/create'), error: $this->firstContextError($context), submitted: $form), 422);
            }

            $this->flashMessages?->success($this->t('Page created successfully.'), 'page.created');

            return Response::redirect($this->adminUrl('/pages/edit?id=' . $createdId));
        } catch (RuntimeException $exception) {
            $this->errorHandler?->logException($exception, ['controller' => 'PageAdminController', 'action' => 'create']);
            $this->flashMessages?->error($this->t('Unable to create page. Please review the form.'), 'page.create_failed');

            return $this->html('Create page', $this->form($this->adminUrl('/pages/create'), error: $exception->getMessage(), submitted: $form), 422);
        }
    }

    /**
     * @param array<string, scalar|null> $parameters
     */
    private function t(string $message, array $parameters = []): string
    {
        $translator = $this->adminContextTranslatorResolver?->resolveForAdminUser($this->guard->user())
            ?? $this->translator
            ?? $this->defaultTranslator();

        return $translator->translate($message, $parameters);
    }

    private function defaultTranslator(): TranslatorInterface
    {
        return new IdentityTranslator();
    }

    /**
     * @param array<string, mixed> $form
     */
    private function processPageForm(string $action, array $form, ?Page $page, AdminUser $user): ?string
    {
        $registry = $this->pageFormProcessors ?? $this->defaultPageFormProcessorRegistry();
        $result = $registry->process('page.form', $form, [
            'action' => $action,
            'page' => $page,
            'user' => $user,
        ]);

        if ($result->valid) {
            return null;
        }

        return implode(' ', $result->errors);
    }

    private function defaultPageFormProcessorRegistry(): AdminFormProcessorRegistry
    {
        $factory = $this->adminFormProcessorConfigFactory ?? new AdminFormProcessorConfigFactory();
        $rootConfig = $this->config?->array('admin_forms') ?? [];
        $moduleConfig = (new AdminFormConfigAggregator($this->projectRootPath()))->aggregate($rootConfig);

        return $factory->create($moduleConfig);
    }

    /**
     * Run a persistence closure through the entity save lifecycle when a runner
     * is injected, falling back to a direct save when it is not.
     *
     * @param array<string, mixed> $form
     * @param callable(EntitySaveContext): void $save
     */
    private function runEntitySave(string $entityType, array $form, int|string|null $entityId, callable $save): EntitySaveContext
    {
        $data = (new EntityDataObject())->addData($form);
        $context = new EntitySaveContext($entityType, $data, new FieldDefinitionRegistry(), $entityId);

        if ($this->saveLifecycle !== null) {
            return $this->saveLifecycle->run($context, $save);
        }

        $save($context);

        return $context;
    }

    private function normaliseSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?: '';

        return trim($slug, '-');
    }

    private function sanitiseContent(string $content): string
    {
        return $this->htmlSanitizer?->sanitise($content)->toString() ?? $content;
    }

    private function normaliseContentJson(mixed $value): ?string
    {
        $json = trim((string)($value ?? ''));
        if ($json === '') {
            return null;
        }

        $decoded = json_decode($json, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Invalid Editor.js JSON payload.');
        }

        $contentModelConfig = $this->config?->array('content_model') ?? [];
        $validator = new BlockJsonValidator($contentModelConfig['block_json'] ?? []);
        $result = $validator->validate($decoded);
        if (!$result->valid) {
            throw new RuntimeException('Invalid Editor.js JSON payload: ' . implode(' ', $result->errors));
        }

        return json_encode($decoded, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: null;
    }

    private function normaliseOptionalString(mixed $value): ?string
    {
        $value = trim((string)($value ?? ''));

        return $value === '' ? null : $value;
    }

    /**
     * Flatten accumulated lifecycle errors into a single message string.
     */
    private function firstContextError(EntitySaveContext $context): string
    {
        $messages = [];
        foreach ($context->errors() as $fieldErrors) {
            foreach ($fieldErrors as $message) {
                $messages[] = (string)$message;
            }
        }

        return $messages === [] ? $this->t('Please review the form.') : implode(' ', $messages);
    }

    public function editForm(Request $request): Response
    {
        if ($this->requirePageManager() === null) {
            return Response::redirect($this->adminUrl('/login'));
        }

        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return $this->html($this->t('Page not found'), '<p>' . $this->e($this->t('Page not found.')) . '</p>', 404);
        }

        return $this->html('Edit page', $this->form($this->adminUrl('/pages/edit?id=' . $page->id), $page));
    }

    private function pageFromRequest(Request $request): ?Page
    {
        $id = $request->query('id');

        return $id !== null && ctype_digit($id) ? $this->pages->findById((int)$id) : null;
    }

    public function update(Request $request): Response
    {
        $user = $this->requirePageManager();
        if ($user === null) {
            return Response::redirect($this->adminUrl('/login'));
        }

        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return $this->html($this->t('Page not found'), '<p>' . $this->e($this->t('Page not found.')) . '</p>', 404);
        }

        $form = $request->form();

        $processorError = $this->processPageForm('update', $form, $page, $user);
        if ($processorError !== null) {
            $this->flashMessages?->error($this->t('Unable to save page. Please review the form.'), 'page.processor_save_failed');

            return $this->html('Edit page', $this->form($this->adminUrl('/pages/edit?id=' . $page->id), $page, $processorError, $form), 422);
        }

        try {
            $context = $this->runEntitySave('page', $form, $page->id, function (EntitySaveContext $c) use ($form, $page, $user): void {
                $this->pages->update(
                    id: $page->id,
                    siteId: (int)($form['site_id'] ?? 0),
                    title: trim((string)($form['title'] ?? '')),
                    slug: $this->normaliseSlug((string)($form['slug'] ?? '')),
                    content: $this->sanitiseContent((string)($form['content'] ?? '')),
                    userId: $user->id,
                    contentFormat: 'html',
                    contentJson: $this->normaliseContentJson($form['content_json'] ?? null),
                    metaTitle: $this->normaliseOptionalString($form['meta_title'] ?? null),
                    metaDescription: $this->normaliseOptionalString($form['meta_description'] ?? null),
                    metaKeywords: $this->normaliseOptionalString($form['meta_keywords'] ?? null),
                    canonicalUrl: $this->normaliseOptionalString($form['canonical_url'] ?? null),
                );

                if (isset($form['publish'])) {
                    $this->pages->publish($page->id, $user->id);
                }
            });

            if ($context->hasErrors()) {
                $this->flashMessages?->error($this->t('Unable to save page. Please review the form.'), 'page.save_failed');

                return $this->html('Edit page', $this->form($this->adminUrl('/pages/edit?id=' . $page->id), $page, $this->firstContextError($context), $form), 422);
            }

            $this->flashMessages?->success($this->t('Page saved successfully.'), 'page.saved');

            return Response::redirect($this->adminUrl('/pages/edit?id=' . $page->id));
        } catch (RuntimeException $exception) {
            $this->errorHandler?->logException($exception, ['controller' => 'PageAdminController', 'action' => 'update']);
            $this->flashMessages?->error($this->t('Unable to save page. Please review the form.'), 'page.save_failed');

            return $this->html('Edit page', $this->form($this->adminUrl('/pages/edit?id=' . $page->id), $page, $exception->getMessage(), $form), 422);
        }
    }

    public function publish(Request $request): Response
    {
        return $this->changeStatus($request, true);
    }

    private function changeStatus(Request $request, bool $publish): Response
    {
        $user = $this->requirePageManager();
        if ($user === null) {
            return Response::redirect($this->adminUrl('/login'));
        }

        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return $this->html($this->t('Page not found'), '<p>' . $this->e($this->t('Page not found.')) . '</p>', 404);
        }

        if ($publish) {
            $this->pages->publish($page->id, $user->id);
            $this->events?->dispatch(PageEvents::PUBLISHED, new PagePublishedEvent($page->id, $user->id));
        } else {
            $this->pages->unpublish($page->id, $user->id);
            $this->events?->dispatch(PageEvents::UNPUBLISHED, new PageUnpublishedEvent($page->id, $user->id));
        }

        $this->flashMessages?->success(
            $publish ? $this->t('Page published successfully.') : $this->t('Page unpublished successfully.'),
            $publish ? 'page.published' : 'page.unpublished',
        );

        return Response::redirect($this->adminUrl('/pages'));
    }

    public function unpublish(Request $request): Response
    {
        return $this->changeStatus($request, false);
    }

    public function preview(Request $request): Response
    {
        if ($this->requirePageManager() === null) {
            return Response::redirect($this->adminUrl('/login'));
        }

        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return Response::html('<h1>Page not found</h1>', 404);
        }

        $site = $this->sites->findById($page->siteId);
        if ($site === null) {
            return Response::html('<h1>Site not found</h1>', 404);
        }

        return Response::html($this->renderer->render($page, $site));
    }
}
