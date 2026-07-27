<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Audit\AuditLogGrid;
use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Grid\GridColumnRegistry;
use Zoosper\Core\Grid\GridCriteria;
use Zoosper\Core\Grid\GridDefinition;
use Zoosper\Core\Grid\GridHtmlRenderer;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * Phase B2: index() now runs the base AuditLogGrid definition through
 * GridColumnRegistry::apply() before building criteria, so any OTHER module
 * can contribute extra columns/filters to this grid via its own
 * config/grid_columns.php — with ZERO changes required here or in
 * AuditLogGrid.php.
 */
final readonly class AuditLogController
{
    public function __construct(
        private SessionGuard $guard,
        private AuditLogRepository $logs,
        private AdminLayout $layout,
        private ?AdminViewRenderer $views = null,
        private ?GridColumnRegistry $columnRegistry = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $definition = $this->columnRegistry !== null
            ? $this->columnRegistry->apply('audit-log', AuditLogGrid::definition())
            : AuditLogGrid::definition();

        $criteria = GridCriteria::fromValues($this->queryValues($request, $definition), $definition);
        $result = $this->logs->paginate($criteria);
        $gridHtml = (new GridHtmlRenderer())->render($definition, $result, $criteria, '/admin/audit-log');

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Audit Log',
                template: 'zoosper-admin::audit-log/index',
                data: ['gridHtml' => $gridHtml],
                user: $user,
                active: 'audit-log',
            ));
        }

        return Response::html($this->layout->render('Audit Log', $gridHtml, $user, 'audit-log'));
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
