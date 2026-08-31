<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Controller;

use RuntimeException;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\I18n\AdminContextTranslatorResolver;
use Zoosper\Core\I18n\IdentityTranslator;
use Zoosper\Core\I18n\TranslatorInterface;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Page\Application\Save\PageSaveCoordinator;
use Zoosper\Page\Admin\Form\PageAdminFormRenderer as LegacyPageFormRenderer;
use Zoosper\Page\Admin\PageAdminGridResponder;
use Zoosper\Page\Admin\PageAdminPreviewResponder;
use Zoosper\Page\Admin\PageRevisionAdminResponder;
use Zoosper\Page\Application\Publication\PagePublicationCoordinator;
use Zoosper\Page\Admin\Lifecycle\PageLifecycleAdminResponder;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Core\Form\AdminFormRegistry;
use Zoosper\Core\Form\AdminFormRenderer;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Editor\ContentEditorInterface;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Thin Admin HTTP adapter for CMS Pages.
 *
 * Runtime ownership is delegated to Page-owned Grid, form, save, publication
 * and preview collaborators. Shared Admin presentation contracts remain
 * intentionally package-owned until their complete cross-module migration is
 * performed as a dedicated compatibility phase.
 */
final readonly class PageAdminController
{
    public function __construct(
        private SessionGuard                     $guard,
        private CsrfTokenManager                 $csrf,
        private PageRepository                   $pages,
        private AdminLayoutRendererInterface     $layout,
        private ?PageAdminGridResponder           $gridResponder = null,
        private ?PageAdminPreviewResponder        $previewResponder = null,
        private ?FlashMessageStoreInterface      $flashMessages = null,
        private ?TranslatorInterface             $translator = null,
        private ?AdminContextTranslatorResolver  $adminContextTranslatorResolver = null,
        private ?LegacyPageFormRenderer          $legacyFormRenderer = null,
        private ?PageSaveCoordinator              $pageSaver = null,
        private ?PagePublicationCoordinator       $publication = null,
        private ?AdminUrlGenerator                 $adminUrls = null,
        private ?PageRevisionAdminResponder         $revisionResponder = null,
        private ?PageLifecycleAdminResponder        $lifecycleResponder = null,
        private ?AdminFormRegistry                 $formRegistry = null,
        private ?AdminFormRenderer                 $formRenderer = null,
        private ?AdminViewRendererInterface        $views = null,
        private ?SiteRepository                    $sites = null,
        private ?ContentEditorInterface            $contentEditor = null,
    )
    {
    }

    public function index(Request $request): Response
    {
        if ($this->gridResponder === null) {
            throw new RuntimeException('Page Admin Grid responder is unavailable.');
        }
        return $this->gridResponder->index($request, $this->currentAdminUser());
    }

    public function gridMutation(Request $request): Response
    {
        if ($this->gridResponder === null) {
            throw new RuntimeException('Page Admin Grid responder is unavailable.');
        }
        return $this->gridResponder->mutate($request, $this->currentAdminUser());
    }

    private function adminUrl(string $path = '', array $query = []): string
    {
        if ($this->adminUrls !== null) {
            return $this->adminUrls->url($path, $query);
        }

        $adminConfig = $this->config?->array('admin') ?? [];
        $basePath = rtrim((string) ($adminConfig['base_path'] ?? '/admin'), '/');
        $url = $path === '' ? $basePath : $basePath . '/' . ltrim($path, '/');
        $queryString = http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return $queryString === '' ? $url : $url . '?' . $queryString;
    }

    private function html(string $title, string $content, int $statusCode = 200): Response
    {
        return Response::html($this->layout->render($title, $content, $this->guard->user(), 'pages'), $statusCode);
    }

    public function createForm(Request $request): Response
    {
        $user = $this->currentAdminUser();

        if ($this->formRegistry !== null && $this->formRenderer !== null && $this->views !== null) {
            return Response::html($this->renderUnifiedForm('Create page', $this->adminUrl('/pages/create'), null, []), 200);
        }

        return $this->html('Create page', $this->renderForm($this->adminUrl('/pages/create')));
    }

    private function renderUnifiedForm(string $title, string $action, ?Page $page = null, array $submitted = [], ?string $error = null, int $revisionPage = 1): string
    {
        $formDef = $this->formRegistry->get('admin.pages.form');

        $siteOptions = [];
        if ($this->sites !== null) {
            foreach ($this->sites->allActive() as $site) {
                $siteOptions[(string) $site->id] = $site->name . ' (' . $site->code . ')';
            }
        }

        $fields = $formDef->fields;
        foreach ($fields as $key => $field) {
            if ($field->name === 'site_id') {
                $fields[$key] = new \Zoosper\Core\Form\AdminFormField(
                    $field->name,
                    $field->type,
                    $field->label,
                    $field->sortOrder,
                    $field->section,
                    ['options' => $siteOptions]
                );
            }
        }

        $content = (string) ($submitted['content'] ?? $page?->content ?? '');
        $contentJson = (string) ($submitted['content_json'] ?? $page?->contentJson ?? '');

        $editorHtml = '';
        if ($this->contentEditor !== null) {
            $editorHtml = $this->contentEditor->render('content', $content, [
                'label' => 'Content', 'rows' => 14, 'required' => true,
                'page' => $page, 'content_json' => $contentJson,
            ]);
        } else {
            $editorHtml = '<input type="hidden" name="content_json" value="' . htmlspecialchars($contentJson, ENT_QUOTES) . '">'
                . '<textarea name="content" rows="14" class="form-control" required>' . htmlspecialchars($content, ENT_QUOTES) . '</textarea>';
        }

        foreach ($fields as $key => $field) {
            if ($field->name === 'content_html') {
                $fields[$key] = new \Zoosper\Core\Form\AdminFormField(
                    $field->name,
                    'html',
                    $field->label,
                    $field->sortOrder,
                    $field->section,
                    ['html' => $editorHtml]
                );
            }
        }

        $dynamicFormDef = new \Zoosper\Core\Form\AdminFormDefinition($formDef->handle, $fields, $formDef->sections);

        $values = $submitted ?: [
            'site_id' => $page?->siteId,
            'title' => $page?->title,
            'slug' => $page?->slug,
            'status' => $page?->status ?? 'draft',
            'meta_title' => $page?->metaTitle,
            'meta_description' => $page?->metaDescription,
            'meta_keywords' => $page?->metaKeywords,
            'canonical_url' => $page?->canonicalUrl,
            'publish' => $page?->isPublished(),
        ];

        $formHtml = $this->formRenderer->render($dynamicFormDef, $values, $action, 'POST', $error ? ['_form' => $error] : [], $this->adminUrl('/pages'), $this->csrf->token());

        $historyHtml = $page !== null ? ($this->revisionResponder?->historyHtml($page, $revisionPage) ?? '') : '';
        $lifecycleHtml = $page !== null ? ($this->lifecycleResponder?->actionsHtml($page) ?? '') : '';

        $html = '
        <div class="admin-page-workspace">
            <header class="page-header">
                <div class="page-header__copy">
                    <p class="page-header__eyebrow">Pages · Content</p>
                    <h1>' . ($page ? 'Edit page' : 'Create page') . '</h1>
                </div>
                <div class="page-header__actions">
                    <a class="button button--secondary" href="' . htmlspecialchars($this->adminUrl('/pages'), ENT_QUOTES) . '">Back to pages</a>
                </div>
            </header>
            ' . ($error ? '<div class="admin-alert admin-alert--danger">' . htmlspecialchars($error, ENT_QUOTES) . '</div>' : '') . '
            ' . $formHtml . '
            ' . ($historyHtml !== '' ? '<section class="admin-page-history">' . $historyHtml . '</section>' : '') . '
            ' . ($lifecycleHtml !== '' ? '<section class="admin-page-lifecycle">' . $lifecycleHtml . '</section>' : '') . '
        </div>';

        return $this->views->render($title, 'zoosper-admin::admin/raw_content', ['content' => $html], $this->guard->user(), 'pages');
    }

    /** @param array<string, mixed> $submitted */
    private function renderForm(string $action, ?Page $page = null, ?string $error = null, array $submitted = []): string
    {
        if ($this->legacyFormRenderer === null) {
            throw new RuntimeException('Page Admin form renderer is unavailable.');
        }
        return $this->legacyFormRenderer->render($action, $page, $error, $submitted);
    }

    public function create(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $form = $request->form();
        $result = $this->pageSaver?->create($form, $user);
        if ($result === null) {
            throw new RuntimeException('Page save coordinator is unavailable.');
        }
        if (!$result->successful) {
            $key = $result->processorRejected ? 'page.processor_create_failed' : 'page.create_failed';
            $this->flashMessages?->error($this->t('Unable to create page. Please review the form.'), $key);

            if ($this->formRegistry !== null && $this->formRenderer !== null && $this->views !== null) {
                return Response::html($this->renderUnifiedForm('Create page', $this->adminUrl('/pages/create'), null, $form, $result->error), 422);
            }

            return $this->html('Create page', $this->renderForm($this->adminUrl('/pages/create'), error: $result->error, submitted: $form), 422);
        }
        $this->flashMessages?->success($this->t('Page created successfully.'), 'page.created');
        return Response::redirect($this->adminUrl('/pages/' . $result->pageId . '/edit'));
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

    public function editForm(Request $request): Response
    {
        $this->currentAdminUser();

        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return $this->html($this->t('Page not found'), '<p>' . $this->e($this->t('Page not found.')) . '</p>', 404);
        }

        if ($this->formRegistry !== null && $this->formRenderer !== null && $this->views !== null) {
            $revisionPage = $request->query('revision_page');
            $revisionPage = $revisionPage !== null && ctype_digit($revisionPage) ? max(1, (int) $revisionPage) : 1;

            return Response::html($this->renderUnifiedForm(
                'Edit page',
                $this->adminUrl('/pages/' . $page->id . '/edit'),
                $page,
                [],
                null,
                $revisionPage
            ), 200);
        }

        $content = $this->renderForm($this->adminUrl('/pages/' . $page->id . '/edit'), $page);
        $revisionPage = $request->query('revision_page');
        $revisionPage = $revisionPage !== null && ctype_digit($revisionPage) ? max(1, (int) $revisionPage) : 1;
        $content .= $this->revisionResponder?->historyHtml($page, $revisionPage) ?? '';
        $content .= $this->lifecycleResponder?->actionsHtml($page) ?? '';
        return $this->html('Edit page', $content);
    }

    private function pageFromRequest(Request $request): ?Page
    {
        // Parameterised routes are canonical; the query value remains as a temporary compatibility fallback.
        $id = $request->routeParam('id') ?? $request->query('id');

        return $id !== null && ctype_digit($id) ? $this->pages->findById((int) $id) : null;
    }

    public function update(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return $this->html($this->t('Page not found'), '<p>' . $this->e($this->t('Page not found.')) . '</p>', 404);
        }
        $form = $request->form();
        $result = $this->pageSaver?->update($form, $page, $user);
        if ($result === null) {
            throw new RuntimeException('Page save coordinator is unavailable.');
        }
        if (!$result->successful) {
            $key = $result->processorRejected ? 'page.processor_save_failed' : 'page.save_failed';
            $this->flashMessages?->error($this->t('Unable to save page. Please review the form.'), $key);

            if ($this->formRegistry !== null && $this->formRenderer !== null && $this->views !== null) {
                return Response::html($this->renderUnifiedForm($this->t('Edit page'), $this->adminUrl('/pages/' . $page->id . '/edit'), $page, $form, $result->error), 422);
            }

            return $this->html('Edit page', $this->renderForm($this->adminUrl('/pages/' . $page->id . '/edit'), $page, $result->error, $form), 422);
        }
        $this->flashMessages?->success($this->t('Page saved successfully.'), 'page.saved');
        return Response::redirect($this->adminUrl('/pages/' . $page->id . '/edit'));
    }

    public function publish(Request $request): Response
    {
        return $this->changeStatus($request, true);
    }

    private function changeStatus(Request $request, bool $publish): Response
    {
        $user = $this->currentAdminUser();
        $page = $this->pageFromRequest($request);
        if ($page === null) {
            return $this->html($this->t('Page not found'), '<p>' . $this->e($this->t('Page not found.')) . '</p>', 404);
        }
        if ($this->publication === null) {
            throw new RuntimeException('Page publication coordinator is unavailable.');
        }
        $publish
            ? $this->publication->publish($page, $user->id)
            : $this->publication->unpublish($page, $user->id);
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
        $this->currentAdminUser();
        if ($this->previewResponder === null) {
            throw new RuntimeException('Page Admin preview responder is unavailable.');
        }
        return $this->previewResponder->respond($this->pageFromRequest($request));
    }

    public function archive(Request $request): Response
    {
        return $this->lifecycleOperation($request, 'archive');
    }
    public function restore(Request $request): Response
    {
        return $this->lifecycleOperation($request, 'restore');
    }
    public function deletePermanently(Request $request): Response
    {
        return $this->lifecycleOperation($request, 'delete');
    }
    private function lifecycleOperation(Request $request, string $operation): Response
    {
        $actor = $this->currentAdminUser();
        $page = $this->pageFromRequest($request);
        if ($page === null || $this->lifecycleResponder === null) {
            return $this->html($this->t('Page not found'), '<p>' . $this->e($this->t('Page not found.')) . '</p>', 404);
        }
        return match ($operation) {
            'archive' => $this->lifecycleResponder->archive($page, $actor),
            'restore' => $this->lifecycleResponder->restore($page, $actor),
            'delete' => $this->lifecycleResponder->delete($page, $actor),
            default => throw new RuntimeException('Unsupported Page lifecycle operation.'),
        };
    }
    public function revisionHistory(Request $request): Response
    {
        $this->currentAdminUser();
        $page = $this->pageFromRequest($request);
        $revisionPage = $request->query('revision_page');
        $revisionPage = $revisionPage !== null && ctype_digit($revisionPage) ? max(1, (int) $revisionPage) : 1;
        if ($page === null || $this->revisionResponder === null) {
            return Response::html('Revision history unavailable.', 404);
        }

        return $this->revisionResponder->historyFragment($page, $revisionPage);
    }

    public function revisionPreview(Request $request): Response
    {
        $this->currentAdminUser();
        $page = $this->pageFromRequest($request);
        $revisionId = $request->routeParam('revisionId');
        if ($page === null || $revisionId === null || !ctype_digit($revisionId) || $this->revisionResponder === null) {
            return $this->html('Revision not found', '<p>Revision not found.</p>', 404);
        }
        return $this->revisionResponder->preview($page, (int) $revisionId);
    }

    public function restoreRevision(Request $request): Response
    {
        $actor = $this->currentAdminUser();
        $page = $this->pageFromRequest($request);
        $revisionId = $request->routeParam('revisionId');
        if ($page === null || $revisionId === null || !ctype_digit($revisionId) || $this->revisionResponder === null) {
            return $this->html('Revision not found', '<p>Revision not found.</p>', 404);
        }
        return $this->revisionResponder->restore($page, (int) $revisionId, $actor);
    }

    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

