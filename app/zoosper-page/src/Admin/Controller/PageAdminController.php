<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Message\FlashMessageStoreInterface;
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\I18n\AdminContextTranslatorResolver;
use Zoosper\Core\I18n\IdentityTranslator;
use Zoosper\Core\I18n\TranslatorInterface;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Page\Admin\Save\PageSaveCoordinator;
use Zoosper\Page\Admin\Form\PageAdminFormRenderer;
use Zoosper\Page\Admin\PageAdminGridResponder;
use Zoosper\Page\Admin\PageAdminPreviewResponder;
use Zoosper\Page\Admin\Publication\PagePublicationCoordinator;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;

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
        private PageRepository                   $pages,
        private AdminLayoutRendererInterface     $layout,
        private ?PageAdminGridResponder           $gridResponder = null,
        private ?PageAdminPreviewResponder        $previewResponder = null,
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
        if ($this->previewResponder === null) {
            throw new RuntimeException('Page Admin preview responder is unavailable.');
        }
        return $this->previewResponder->respond($this->pageFromRequest($request));
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

