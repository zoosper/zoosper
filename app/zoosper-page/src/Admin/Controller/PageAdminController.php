<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Controller;

use RuntimeException;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\AdminGrid\GridBulkActionManifestRenderer;
use Zoosper\Grid\BulkAction\GridBulkActionManifest;
use Zoosper\AdminGrid\GridWorkspaceRequest;
use Zoosper\Admin\Message\FlashMessageStoreInterface;
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridHtmlRenderer;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\I18n\AdminContextTranslatorResolver;
use Zoosper\Core\I18n\IdentityTranslator;
use Zoosper\Core\I18n\TranslatorInterface;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Page\Admin\PageGridDataSource;
use Zoosper\Page\Admin\PageGridDefinition;
use Zoosper\Page\Admin\PageGridRepository;
use Zoosper\Page\Admin\PageGridQueryState;
use Zoosper\Page\Admin\PageGridBulkActions;
use Zoosper\Page\Admin\PageGridMutationCoordinator;
use Zoosper\Page\Admin\PageGridWorkspace;
use Zoosper\Page\Admin\Save\PageSaveCoordinator;
use Zoosper\Page\Admin\Form\PageAdminFormRenderer;
use Zoosper\Page\Admin\Publication\PagePublicationCoordinator;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Service\PageRenderer;
use Zoosper\Site\Repository\SiteRepository;

/**
 * Admin CRUD controller for CMS pages.
 *
 * Phase 1.41 (partial, round 3a): `layout` and `views` typed to
 * Zoosper\Auth\Layout\AdminLayoutRendererInterface and
 * Zoosper\Auth\UI\AdminViewRendererInterface.
 *
 * Phase 1.41 (page decoupling, part A): AdminFormSection,
 * AdminFormSectionProviderInterface (used transitively via the four
 * Page*SectionProvider classes), AdminFormProviderRegistry,
 * AdminFormRenderer, AdminFormConfigProviderFactory,
 * AdminFormProcessorRegistry, AdminFormProcessorConfigFactory,
 * AdminFormProcessorInterface, AdminFormProcessingResult all relocated to
 * Zoosper\Core\Form. Only import/type references changed; behaviour is
 * identical.
 *
 * NOTE: this controller still imports and, as a fallback, instantiates
 * AdminFormConfigAggregator (this class stays in the admin module for now,
 * pending a follow-up phase — it is protected by tests with hardcoded
 * assertions that need separate, careful updating).
 */
final readonly class PageAdminController
{
    public function __construct(
        private SessionGuard                     $guard,
        private CsrfTokenManager                 $csrf,
        private PageRepository                   $pages,
        private SiteRepository                   $sites,
        private PageRenderer                     $renderer,
        private AdminLayoutRendererInterface     $layout,
        private AdminViewRendererInterface       $views,
        private ?PageGridRepository              $pageGrid = null,
        private ?PageGridDefinition              $pageGridDefinition = null,
        private ?PageGridDataSource              $pageGridDataSource = null,
        private ?GridHtmlRenderer                $gridHtmlRenderer = null,
        private ?PageGridWorkspace               $pageGridWorkspace = null,
        private ?GridWorkspaceMutationFormsRenderer $gridMutationForms = null,
        private ?GridBulkActionManifestRenderer   $gridBulkManifest = null,
        private ?PageGridMutationCoordinator      $pageGridMutations = null,
        private ?FlashMessageStoreInterface      $flashMessages = null,
        private ?TranslatorInterface             $translator = null,
        private ?AdminContextTranslatorResolver  $adminContextTranslatorResolver = null,
        private ?PageAdminFormRenderer           $formRenderer = null,
        private ?PageSaveCoordinator              $pageSaver = null,
        private ?PagePublicationCoordinator       $publication = null,
        private ?AdminUrlGenerator                 $adminUrls = null,
    )
    {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $resolved = $this->pageGridWorkspace?->resolve(
            $user->id,
            PageGridQueryState::fromQuery($request->queryParams()),
            PageGridQueryState::bookmarkId($request->queryParams()),
        );
        $definition = $resolved['state']->definition ?? $this->pageGridDefinition?->build();
        $criteria = $resolved['state']->criteria ?? ($definition !== null
            ? GridCriteria::fromValues($request->queryParams(), $definition)
            : null);
        $pagination = $criteria !== null
            ? $this->pageGridDataSource?->paginate($criteria)
            : null;
        $tableHtml = $definition !== null && $criteria !== null && $pagination !== null
            ? $this->gridHtmlRenderer?->renderBody($definition, $pagination, $criteria, $this->adminUrl('/pages'))
            : null;
        $workspaceHtml = $resolved['html'] ?? '';
        if ($resolved !== null && $this->gridMutationForms !== null) {
            $workspaceHtml .= $this->gridMutationForms->render(
                $resolved['state'],
                $this->adminUrl('/pages/grid'),
                '_csrf',
                $this->csrf->token(),
            );
        }
        if ($this->gridBulkManifest !== null) {
            $workspaceHtml .= $this->gridBulkManifest->render(
                new GridBulkActionManifest(
                    PageGridWorkspace::GRID_KEY,
                    PageGridBulkActions::definitions(),
                ),
            );
            $workspaceHtml = str_replace(
                ' data-grid-bulk-action-manifest>',
                ' data-grid-bulk-action-manifest data-csrf-token="'
                    . htmlspecialchars($this->csrf->token(), ENT_QUOTES, 'UTF-8')
                    . '" data-server-action="' . $this->e($this->adminUrl('/pages/bulk-action')) . '">',
                $workspaceHtml,
            );
        }
        $gridHtml = $workspaceHtml . ($tableHtml ?? '');
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
                'gridHtml' => $gridHtml,
                'createUrl' => $this->adminUrl('/pages/create'),
            ],
            $user,
            'pages',
        ));
    }
public function gridMutation(Request $request): Response
{
    $user = $this->currentAdminUser();
    if ($this->pageGridMutations === null) {
        return Response::html('Pages Grid mutations are unavailable.', 503);
    }

    $result = $this->pageGridMutations->mutate(
        $user->id,
        new GridWorkspaceRequest('POST', $request->queryParams(), $request->form()),
    );
    $this->flashMessages?->success($result->message, 'pages.grid.' . $result->action);

    return Response::redirect($result->redirectPath);
}
    /** @param array<string, scalar|null> $query */
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
        $this->currentAdminUser();

        return $this->html('Create page', $this->renderForm($this->adminUrl('/pages/create')));
    }

    /** @param array<string, mixed> $submitted */
    private function renderForm(string $action, ?Page $page = null, ?string $error = null, array $submitted = []): string
    {
        if ($this->formRenderer === null) {
            throw new RuntimeException('Page Admin form renderer is unavailable.');
        }
        return $this->formRenderer->render($action, $page, $error, $submitted);
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

        return $this->html('Edit page', $this->renderForm($this->adminUrl('/pages/' . $page->id . '/edit'), $page));
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

    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}

