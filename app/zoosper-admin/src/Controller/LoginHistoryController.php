<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Audit\LoginHistoryGrid;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Grid\GridColumnRegistry;
use Zoosper\Grid\GridCriteria;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridHtmlRenderer;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;

/**
 * Phase B2: index() now runs the base LoginHistoryGrid definition through
 * GridColumnRegistry::apply() before building criteria. As a live proof, the
 * zoosper-two-factor module contributes a "User Agent" column via
 * app/zoosper-two-factor/config/grid_columns.php — real, already-captured
 * data that was never surfaced before, added with ZERO changes to this file
 * or LoginHistoryGrid.php.
 */
final readonly class LoginHistoryController
{
    public function __construct(
        private SessionGuard $guard,
        private LoginHistoryRepository $history,
        private AdminLayout $layout,
        private ?AdminViewRenderer $views = null,
        private ?GridColumnRegistry $columnRegistry = null,
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $definition = $this->columnRegistry !== null
            ? $this->columnRegistry->apply('login-history', LoginHistoryGrid::definition())
            : LoginHistoryGrid::definition();

        $criteria = GridCriteria::fromValues($this->queryValues($request, $definition), $definition);
        $result = $this->history->paginate($criteria);
        $gridHtml = (new GridHtmlRenderer())->render($definition, $result, $criteria, $this->adminUrls?->url('login-history') ?? '/admin/login-history');

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

