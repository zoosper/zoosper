<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

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
        $user = $this->guard->requirePermission('role.manage');
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $rows = $this->logs->latest();
        if ($this->views !== null) {
            return Response::html($this->views->render(
                title: 'Audit Log',
                template: 'zoosper-admin::audit-log/index',
                data: ['rows' => $rows],
                user: $user,
                active: 'audit-log',
            ));
        }

        return Response::html($this->layout->render('Audit Log', '<p>Audit log view renderer is not configured.</p>', $user, 'audit-log'));
    }
}
