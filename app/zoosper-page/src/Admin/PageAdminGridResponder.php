<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\AdminGrid\GridBulkActionManifestRenderer;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\AdminGrid\GridWorkspaceRequest;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\BulkAction\GridBulkActionManifest;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridHtmlRenderer;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Site\Repository\SiteRepository;

/** Owns the complete Pages Grid screen and protected workspace mutation response. */
final readonly class PageAdminGridResponder
{
    public function __construct(
        private PageRepository $pages,
        private SiteRepository $sites,
        private AdminViewRendererInterface $views,
        private CsrfTokenManager $csrf,
        private ?PageGridDefinition $definition = null,
        private ?PageGridDataSource $dataSource = null,
        private ?GridHtmlRenderer $gridRenderer = null,
        private ?PageGridWorkspace $workspace = null,
        private ?GridWorkspaceMutationFormsRenderer $mutationForms = null,
        private ?GridBulkActionManifestRenderer $bulkManifest = null,
        private ?PageGridMutationCoordinator $mutations = null,
        private ?FlashMessageStoreInterface $flashMessages = null,
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    public function index(Request $request, AdminUser $user): Response
    {
        $query = $request->queryParams();
        $resolved = $this->workspace?->resolve(
            $user->id,
            PageGridQueryState::fromQuery($query),
            PageGridQueryState::bookmarkId($query),
        );
        $definition = $resolved['state']->definition ?? $this->definition?->build();
        $criteria = $resolved['state']->criteria ?? ($definition !== null
            ? GridCriteria::fromValues($query, $definition)
            : null);
        $pagination = $criteria !== null ? $this->dataSource?->paginate($criteria) : null;
        $tableHtml = $definition !== null && $criteria !== null && $pagination !== null
            ? $this->gridRenderer?->renderBody($definition, $pagination, $criteria, $this->adminUrl('/pages'))
            : null;
        $workspaceHtml = $resolved['html'] ?? '';
        if ($resolved !== null && $this->mutationForms !== null) {
            $workspaceHtml .= $this->mutationForms->render(
                $resolved['state'], $this->adminUrl('/pages/grid'), '_csrf', $this->csrf->token(),
            );
        }
        if ($this->bulkManifest !== null) {
            $workspaceHtml .= $this->bulkManifest->render(new GridBulkActionManifest(
                PageGridWorkspace::GRID_KEY,
                PageGridBulkActions::definitions(),
            ));
            $workspaceHtml = str_replace(
                ' data-grid-bulk-action-manifest>',
                ' data-grid-bulk-action-manifest data-csrf-token="'
                    . htmlspecialchars($this->csrf->token(), ENT_QUOTES, 'UTF-8')
                    . '" data-server-action="' . $this->escape($this->adminUrl('/pages/bulk-action')) . '">',
                $workspaceHtml,
            );
        }

        return Response::html($this->views->render(
            'Pages',
            'zoosper-page::admin/pages/index',
            [
                'pages' => $pagination?->items ?? $this->pages->all(),
                'pagination' => $pagination,
                'criteria' => $criteria,
                'sites' => $this->sites->allActive(),
                'gridHtml' => $workspaceHtml . ($tableHtml ?? ''),
                'createUrl' => $this->adminUrl('/pages/create'),
            ],
            $user,
            'pages',
        ));
    }

    public function mutate(Request $request, AdminUser $user): Response
    {
        if ($this->mutations === null) {
            return Response::html('Pages Grid mutations are unavailable.', 503);
        }
        $result = $this->mutations->mutate(
            $user->id,
            new GridWorkspaceRequest('POST', $request->queryParams(), $request->form()),
        );
        $this->flashMessages?->success($result->message, 'pages.grid.' . $result->action);

        return Response::redirect($result->redirectPath);
    }

    private function adminUrl(string $path): string
    {
        return $this->adminUrls?->url(ltrim($path, '/')) ?? '/admin' . $path;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}
