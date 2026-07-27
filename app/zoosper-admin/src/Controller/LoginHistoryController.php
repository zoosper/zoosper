<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Audit\LoginHistoryCriteria;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * Phase 1.112 (Sonnet Phase 2 §4.2): index() now builds a LoginHistoryCriteria
 * from the request query (page/page_size/q/status) and calls the new
 * paginate() method instead of the unbounded latest() "top 100" query.
 *
 * Phase 1.112 hotfix: Request::query() is single-key only
 * (query(string $key, ?string $default = null): ?string) — there is no bulk
 * "all params" getter on Request. The known keys are read individually here and
 * assembled into the array LoginHistoryCriteria::fromQuery() expects.
 *
 * The 'rows' view-data key is preserved so existing templates keep working
 * unchanged; 'pagination' and 'criteria' are ADDED for templates that choose to
 * render page links.
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

        $criteria = LoginHistoryCriteria::fromQuery($this->queryParams($request));
        $result = $this->history->paginate($criteria);

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Login History',
                template: 'zoosper-admin::login-history/index',
                data: [
                    'rows' => $result->items,
                    'pagination' => $result,
                    'criteria' => $criteria,
                    'linkParameters' => $criteria->linkParameters(),
                ],
                user: $user,
                active: 'login-history',
            ));
        }

        return Response::html($this->layout->render('Login History', '<p>Login history view renderer is not configured.</p>', $user, 'login-history'));
    }

    /**
     * Read the query-string keys LoginHistoryCriteria::fromQuery() understands
     * from the request, since Request::query() is single-key only.
     *
     * @return array<string, string>
     */
    private function queryParams(Request $request): array
    {
        $params = [];

        foreach (['page', 'page_size', 'q', 'status'] as $key) {
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
