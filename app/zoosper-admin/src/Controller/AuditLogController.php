<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use RuntimeException;
use Zoosper\Admin\Audit\AuditLogCriteria;
use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

/**
 * Phase 1.112 (Sonnet Phase 2 §4.2): index() now builds an AuditLogCriteria
 * from the request query (page/page_size/q/entity_type) and calls the new
 * paginate() method instead of the unbounded latest() "top 100" query.
 *
 * The 'rows' view-data key is preserved (now sourced from the paginated
 * result's items) so existing templates that only read 'rows' keep working
 * unchanged; 'pagination' and 'criteria' are ADDED for templates that choose to
 * render page links, matching the shape PageGridRepository/PageGridCriteria
 * already established for the Pages admin grid.
 */
final readonly class AuditLogController
{
    public function __construct(
        private SessionGuard $guard,
        private AuditLogRepository $logs,
        private AdminLayout $layout,
        private ?AdminViewRenderer $views = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $criteria = AuditLogCriteria::fromQuery($request->query());
        $result = $this->logs->paginate($criteria);

        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Audit Log',
                template: 'zoosper-admin::audit-log/index',
                data: [
                    'rows' => $result->items,
                    'pagination' => $result,
                    'criteria' => $criteria,
                    'linkParameters' => $criteria->linkParameters(),
                ],
                user: $user,
                active: 'audit-log',
            ));
        }

        return Response::html($this->layout->render('Audit Log', '<p>Audit log view renderer is not configured.</p>', $user, 'audit-log'));
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
