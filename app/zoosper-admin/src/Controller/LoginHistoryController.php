<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Audit\LoginHistoryGrid;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Grid\GridCriteria;
use Zoosper\Core\Grid\GridDefinition;
use Zoosper\Core\Grid\GridHtmlRenderer;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * Phase A (Grid Core): index() now builds GridCriteria from the request query
 * using the shared LoginHistoryGrid definition, calls the repository's generic
 * paginate(GridCriteria), and renders through the ONE shared GridHtmlRenderer.
 *
 * SUPERSEDES Phase 1.112's LoginHistoryCriteria-based wiring.
 */
final readonly class LoginHistoryController
{
    public function __construct(
        private SessionGuard $guard,
        private LoginHistoryRepository $history,
        private AdminLayout $layout,
        private ?AdminViewRenderer $views = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $definition = LoginHistoryGrid::definition();
        $criteria = GridCriteria::fromValues($this->queryValues($request, $definition), $definition);
        $result = $this->history->paginate($criteria);
        $gridHtml = (new GridHtmlRenderer())->render($definition, $result, $criteria, '/admin/login-history');

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Login History',
                template: 'zoosper-admin::login-history/index',
                data: ['gridHtml' => $gridHtml],
                user: $user,
                active: 'login-history',
            ));
        }

        return Response::html($this->layout->render('Login History', $gridHtml, $user, 'login-history'));
    }

    /**
     * @return array<string, string>
     */
    private function queryValues(Request $request, GridDefinition $definition): array
    {
        $params = [];
        $keys = array_merge(['page', 'page_size', 'sort', 'dir'], $definition->filterKeys());

        foreach ($keys as $key) {
            $value = $request->query($key);
            if ($value !== null) {
                $params[$key] = $value;
            }
        }

        return $params;
    }

    /**
     * Return the authenticated admin user after the middleware permission gate.
     */
    private function currentAdminUser(): \Zoosper\Auth\Model\AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}
