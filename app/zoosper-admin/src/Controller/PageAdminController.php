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
use Zoosper\Core\Html\HtmlSanitizerInterface;
use Zoosper\Core\I18n\IdentityTranslator;
use Zoosper\Core\I18n\TranslatorInterface;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Page\Admin\Form\PageContentSectionProvider;
use Zoosper\Page\Admin\Form\PageDetailsSectionProvider;
use Zoosper\Page\Admin\Form\PagePublishingSectionProvider;
use Zoosper\Page\Admin\Form\PageSeoSectionProvider;
use Zoosper\Page\Admin\PageGridCriteria;
use Zoosper\Page\Admin\PageGridRepository;
use Zoosper\Page\Content\BlockJsonValidator;
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
 */
final readonly class PageAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private PageRepository $pages,
        private SiteRepository $sites,
        private PageRenderer $renderer,
        private AdminLayout $layout,
        private ?AdminViewRenderer $views = null,
        private ?PageGridRepository $pageGrid = null,
        private ?HtmlSanitizerInterface $htmlSanitizer = null,
        private ?FlashMessageStoreInterface $flashMessages = null,
        private ?ConfigRepository $config = null,
        private ?ContentEditorInterface $contentEditor = null,
        private ?TranslatorInterface $translator = null,
        private ?AdminFormProviderRegistry $pageFormSections = null,
        private ?AdminFormRenderer $adminFormRenderer = null,
        private ?AdminFormConfigProviderFactory $adminFormConfigProviderFactory = null,
        private ?AdminFormProcessorRegistry $pageFormProcessors = null,
        private ?AdminFormProcessorConfigFactory $adminFormProcessorConfigFactory = null,
    ) {
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

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Pages',
                template: 'zoosper-page::admin/pages/index',
                data: [
                    'pages' => $pages,
                    'pagination' => $pagination,
                    'criteria' => $criteria,
                    'sites' => $sites,
                ],
                user: $user,
                active: 'pages',
            ));
        }

        $rows = '';
        foreach ($pages as $page) {
            $id = (int) $this->pageValue($page, 'id');
            $title = $this->e((string) $this->pageValue($page, 'title'));
            $slug = $this->e((string) $this->pageValue($page, 'slug'));
            $status = $this->e((string) $this->pageValue($page, 'status'));
            $editUrl = $this->e($this->adminUrl('/pages/edit?id=' . $id));
            $previewUrl = $this->e($this->adminUrl('/pages/preview?id=' . $id));
            $publicLink = $this->isPublishedRow($page)
                ? '<a href="/' . $slug . '" target="_blank">View</a>'
                : '<span class="muted">Draft</span>';

            $rows .= <<<HTML
<tr>
    <td>{$id}</td>
    <td>{$title}</td>
    <td><code>/{$slug}</code></td>
    <td>{$status}</td>
    <td class="actions">
        <a href="{$editUrl}">Edit</a>
        <a href="{$previewUrl}" target="_blank">Preview</a>
        {$publicLink}
        {$this->statusButtonForRow($page)}
    </td>
</tr>
HTML;
        }

        if ($rows === '') {
            $rows = '<tr><td colspan="5">No pages yet.</td></tr>';
        }

        $createUrl = $this->e($this->adminUrl('/pages/create'));

        return $this->html('Pages', <<<HTML
<div class="toolbar">
    <a class="button" href="{$createUrl}">Create page</a>
</div>
<table>
    <thead><tr><th>ID</th><th>Title</th><th>Slug</th><th>Status</th><th>Actions</th></tr></thead>
    <tbody>{$rows}</tbody>
</table>
HTML);
    }

    public function createForm(Request $request): Response
    {
        if ($this->requirePageManager() === null) {
            return Response::redirect($this->adminUrl('/login'));
        }

        return $this->html('Create page', $this->form($this->adminUrl('/pages/create')));
    }

    public function create(Request $request): Response
    {
        $user = $this->requirePageManager();
        if ($user === null) {
            return Response::redirect($this->adminUrl('/login'));
        }

        $form = $request->form();
        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            $this->flashMessages?->error($this->t('Unable to save page. Invalid security token.'), 'page.csrf_failed');

            return $this->html('Create page', $this->form($this->adminUrl('/pages/create'), error: $this->t('Invalid security token.'), submitted: $form), 419);
        }

        $processorError = $this->processPageForm('create', $form, null, $user);
        if ($processorError !== null) {
            $this->flashMessages?->error($this->t('Unable to create page. Please review the form.'), 'page.processor_create_failed');

            return $this->html('Create page', $this->form($this->adminUrl('/pages/create'), error: $processorError, submitted: $form), 422);
        }

        try {
            $id = $this->pages->create(
                siteId: (int) ($form['site_id'] ?? 0),
                title: trim((string) ($form['title'] ?? '')),
                slug: $this->normaliseSlug((string) ($form['slug'] ?? '')),
                content: $this->sanitiseContent((string) ($form['content'] ?? '')),
                status: isset($form['publish']) ? 'published' : 'draft',
                userId: $user->id,
                contentFormat: 'html',
                contentJson: $this->normaliseContentJson($form['content_json'] ?? null),
                metaTitle: $this->normaliseOptionalString($form['meta_title'] ?? null),
                metaDescription: $this->normaliseOptionalString($form['meta_description'] ?? null),
                metaKeywords: $this->normaliseOptionalString($form['meta_keywords'] ?? null),
                canonicalUrl: $this->normaliseOptionalString($form['canonical_url'] ?? null),
            );

            $this->flashMessages?->success($this->t('Page created successfully.'), 'page.created');

            return Response::redirect($this->adminUrl('/pages/edit?id=' . $id));
        } catch (RuntimeException $exception) {
            $this->flashMessages?->error($this->t('Unable to create page. Please review the form.'), 'page.create_failed');

            return $this->html('Create page', $this->form($this->adminUrl('/pages/create'), error: $exception->getMessage(), submitted: $form), 422);
        }
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
        if (!$this->csrf->isValid((string) ($form['_csrf_token'] ?? ''))) {
            $this->flashMessages?->error($this->t('Unable to save page. Invalid security token.'), 'page.csrf_failed');

            return $this->html('Edit page', $this->form($this->adminUrl('/pages/edit?id=' . $page->id), $page, 'Invalid security token.', $form), 419);
        }

        $processorError = $this->processPageForm('update', $form, $page, $user);
        if ($processorError !== null) {
            $this->flashMessages?->error($this->t('Unable to save page. Please review the form.'), 'page.processor_save_failed');

            return $this->html('Edit page', $this->form($this->adminUrl('/pages/edit?id=' . $page->id), $page, $processorError, $form), 422);
        }

        try {
            $this->pages->update(
                id: $page->id,
                siteId: (int) ($form['site_id'] ?? 0),
                title: trim((string) ($form['title'] ?? '')),
                slug: $this->normaliseSlug((string) ($form['slug'] ?? '')),
                content: $this->sanitiseContent((string) ($form['content'] ?? '')),
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

            $this->flashMessages?->success($this->t('Page saved successfully.'), 'page.saved');

            return Response::redirect($this->adminUrl('/pages/edit?id=' . $page->id));
        } catch (RuntimeException $exception) {
            $this->flashMessages?->error($this->t('Unable to save page. Please review the form.'), 'page.save_failed');

            return $this->html('Edit page', $this->form($this->adminUrl('/pages/edit?id=' . $page->id), $page, $exception->getMessage(), $form), 422);
        }
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

    public function publish(Request $request): Response
    {
        return $this->changeStatus($request, true);
    }

    public function unpublish(Request $request): Response
    {
        return $this->changeStatus($request, false);
    }

    private function changeStatus(Request $request, bool $publish): Response
    {
        $user = $this->requirePageManager();
        if ($user === null) {
            return Response::redirect($this->adminUrl('/login'));
        }

        if (!$this->csrf->isValid((string) ($request->form()['_csrf_token'] ?? ''))) {
            $this->flashMessages?->error($this->t('Unable to change page status. Invalid security token.'), 'page.status_csrf_failed');

            return $this->html($this->t('Invalid token'), '<p>' . $this->e($this->t('Invalid security token.')) . '</p>', 419);
        }

        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return $this->html($this->t('Page not found'), '<p>' . $this->e($this->t('Page not found.')) . '</p>', 404);
        }

        $publish ? $this->pages->publish($page->id, $user->id) : $this->pages->unpublish($page->id, $user->id);

        $this->flashMessages?->success(
            $publish ? $this->t('Page published successfully.') : $this->t('Page unpublished successfully.'),
            $publish ? 'page.published' : 'page.unpublished',
        );

        return Response::redirect($this->adminUrl('/pages'));
    }

    private function requirePageManager(): ?AdminUser
    {
        return $this->guard->requirePermission(Permission::PageManage->value);
    }

    private function pageFromRequest(Request $request): ?Page
    {
        $id = $request->query('id');

        return $id !== null && ctype_digit($id) ? $this->pages->findById((int) $id) : null;
    }

    private function sanitiseContent(string $content): string
    {
        return $this->htmlSanitizer?->sanitise($content)->toString() ?? $content;
    }

    /** @param array<string, mixed> $submitted */
    private function form(string $action, ?Page $page = null, ?string $error = null, array $submitted = []): string
    {
        $token = $this->csrf->token();
        $siteId = (int) ($submitted['site_id'] ?? $page?->siteId ?? 0);
        $content = $this->e((string) ($submitted['content'] ?? $page?->content ?? ''));
        $contentJson = $this->e((string) ($submitted['content_json'] ?? $page?->contentJson ?? ''));
        $editorHtml = $this->renderContentEditor($content, $page, $contentJson);
        $errorHtml = $error !== null ? '<p class="error">' . $this->e($error) . '</p>' : '';

        $context = [
            'page' => $page,
            'submitted' => $submitted,
            'siteOptions' => $this->siteOptions($siteId),
            'title' => $this->e((string) ($submitted['title'] ?? $page?->title ?? '')),
            'slug' => $this->e((string) ($submitted['slug'] ?? $page?->slug ?? '')),
            'editorHtml' => $editorHtml,
            'contentJson' => $contentJson,
            'metaTitle' => $this->e((string) ($submitted['meta_title'] ?? $page?->metaTitle ?? '')),
            'metaDescription' => $this->e((string) ($submitted['meta_description'] ?? $page?->metaDescription ?? '')),
            'metaKeywords' => $this->e((string) ($submitted['meta_keywords'] ?? $page?->metaKeywords ?? '')),
            'canonicalUrl' => $this->e((string) ($submitted['canonical_url'] ?? $page?->canonicalUrl ?? '')),
            'publishChecked' => (isset($submitted['publish']) || $page?->isPublished()) ? ' checked' : '',
            'backUrl' => $this->e($this->adminUrl('/pages')),
        ];

        $registry = $this->pageFormSections ?? $this->defaultPageFormSectionRegistry();
        $renderer = $this->adminFormRenderer ?? new AdminFormRenderer();
        $sections = $registry->sectionsFor('page.form', $context);

        return $errorHtml . $renderer->render($action, $token, $sections);
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

    private function statusButton(Page $page): string
    {
        return $this->statusButtonByValues($page->id, $page->isPublished());
    }

    /** @param Page|array<string, mixed> $page */
    private function statusButtonForRow(Page|array $page): string
    {
        if ($page instanceof Page) {
            return $this->statusButton($page);
        }

        return $this->statusButtonByValues((int) $this->pageValue($page, 'id'), $this->isPublishedRow($page));
    }

    private function statusButtonByValues(int $pageId, bool $isPublished): string
    {
        $token = $this->e($this->csrf->token());
        $action = $isPublished ? 'unpublish' : 'publish';
        $label = $isPublished ? 'Unpublish' : 'Publish';
        $url = $this->e($this->adminUrl('/pages/' . $action . '?id=' . $pageId));

        return '<form method="post" action="' . $url . '" class="inline-form">'
            . '<input type="hidden" name="_csrf_token" value="' . $token . '">'
            . '<button type="submit">' . $label . '</button>'
            . '</form>';
    }

    /** @param Page|array<string, mixed> $page */
    private function pageValue(Page|array $page, string $key): mixed
    {
        if ($page instanceof Page) {
            return match ($key) {
                'id' => $page->id,
                'site_id', 'siteId' => $page->siteId,
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'status' => $page->status,
                default => null,
            };
        }

        return $page[$key] ?? null;
    }

    /** @param Page|array<string, mixed> $page */
    private function isPublishedRow(Page|array $page): bool
    {
        return $page instanceof Page ? $page->isPublished() : (string) $this->pageValue($page, 'status') === 'published';
    }

    private function normaliseContentJson(mixed $value): ?string
    {
        $json = trim((string) ($value ?? ''));
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

    private function normaliseSlug(string $slug): string
    {
        $slug = strtolower(trim($slug));
        $slug = preg_replace('/[^a-z0-9]+/i', '-', $slug) ?: '';

        return trim($slug, '-');
    }

    private function normaliseOptionalString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }

    private function html(string $title, string $content, int $statusCode = 200): Response
    {
        return Response::html($this->layout->render($title, $content, $this->guard->user(), 'pages'), $statusCode);
    }

    private function adminUrl(string $path): string
    {
        $adminConfig = $this->config?->array('admin') ?? [];
        $basePath = (string) ($adminConfig['base_path'] ?? '/admin');

        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }


    /**
     * @param array<string, scalar|null> $parameters
     */
    private function t(string $message, array $parameters = []): string
    {
        return ($this->translator ?? new IdentityTranslator())->translate($message, $parameters);
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
