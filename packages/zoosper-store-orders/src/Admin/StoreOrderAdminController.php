<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Admin;

use InvalidArgumentException;
use Throwable;
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\AdminGrid\GridWorkspaceMutationFormsRenderer;
use Zoosper\AdminGrid\GridWorkspaceRequest;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Pagination\PaginationResult;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\Grid\GridHtmlRenderer;
use Zoosper\StoreOrders\StoreOrderDataSourceFactory;

final readonly class StoreOrderAdminController
{
    /** @param array<string, mixed> $config */
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminLayoutRendererInterface $layout,
        private StoreOrderDataSourceFactory $dataSources,
        private StoreOrderGridWorkspace $workspace,
        private StoreOrderGridMutationCoordinator $mutations,
        private GridWorkspaceMutationFormsRenderer $mutationForms,
        private array $config,
        private GridHtmlRenderer $gridRenderer = new GridHtmlRenderer(),
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $values = $_GET;
        $values['store_code'] ??= 3;
        $values['kiosk_website_id'] ??= 55;
        if (isset($values['page_size']) && !in_array((int) $values['page_size'], [5, 10, 20, 50, 100], true)) {
            $values['page_size'] = 20;
        }
        $queryState = StoreOrderGridQueryState::fromQuery($values);

        try {
            $resolved = $this->workspace->resolve(
                adminUserId: $user->id,
                queryState: $queryState,
                bookmarkId: StoreOrderGridQueryState::bookmarkId($values),
            );
            $state = $resolved['state'];
            $result = $this->dataSources->create($this->config, $user->id, [
                'store_code' => $state->criteria->filters['store_code'] ?? 3,
                'kiosk_website_id' => $state->criteria->filters['kiosk_website_id'] ?? 55,
            ])->fetch(new GridQuery(
                page: $state->criteria->pager->page,
                pageSize: $state->criteria->pager->pageSize,
                filters: $state->criteria->filters,
            ));
            $pagination = new PaginationResult(
                items: $result->items,
                total: $result->total,
                page: $result->page,
                pageSize: $result->pageSize,
            );
            $content = '<h1>Store Orders</h1>'
                . $resolved['html']
                . $this->mutationForms->render(
                    $state,
                    StoreOrderGridWorkspace::ACTION,
                    '_csrf_token',
                    $this->csrf->token(),
                )
                . $this->gridRenderer->renderBody(
                    $state->definition,
                    $pagination,
                    $state->criteria,
                    StoreOrderGridWorkspace::ACTION,
                );

            return Response::html($this->layout->render('Store Orders', $content, $user, 'store-orders'));
        } catch (InvalidArgumentException $exception) {
            $content = '<h1>Store Orders</h1><div class="admin-alert admin-alert--error">'
                . htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '</div>';
            return Response::html($this->layout->render('Store Orders', $content, $user, 'store-orders'), 422);
        } catch (Throwable) {
            $content = '<h1>Store Orders</h1>'
                . '<div class="admin-alert admin-alert--error">'
                . 'The Store Orders service is currently unavailable. No empty-result state has been shown.'
                . '</div>';
            return Response::html($this->layout->render('Store Orders', $content, $user, 'store-orders'), 503);
        }
    }
    public function mutate(Request $request): Response
    {
        $user = $this->guard->user();
        if ($user === null) {
            return Response::redirect('/admin/login');
        }
        if (!$this->csrf->isValid(isset($_POST['_csrf_token']) ? (string) $_POST['_csrf_token'] : null)) {
            return Response::html('Invalid CSRF token.', 419);
        }

        try {
            $result = $this->mutations->mutate(
                $user->id,
                new GridWorkspaceRequest('POST', $_GET, $_POST),
            );
            return Response::redirect($result->redirectPath);
        } catch (InvalidArgumentException $exception) {
            return Response::html(
                htmlspecialchars($exception->getMessage(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
                422,
            );
        }
    }

}
