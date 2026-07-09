<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use Zoosper\Admin\Audit\AuditLogRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class AuditLogController
{
    public function __construct(private SessionGuard $guard, private AuditLogRepository $logs, private AdminLayout $layout)
    {
    }

    public function index(Request $request): Response
    {
        $user = $this->guard->requirePermission('role.manage');
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $rows = '';
        foreach ($this->logs->latest() as $row) {
            $rows .= '<tr><td>' . $this->e((string) $row['created_at']) . '</td><td>' . $this->e((string) $row['actor_email']) . '</td><td><code>' . $this->e((string) $row['action']) . '</code></td><td>' . $this->e((string) $row['entity_type']) . '</td><td>' . $this->e((string) $row['entity_id']) . '</td><td>' . $this->e((string) $row['summary']) . '</td></tr>';
        }
        if ($rows === '') { $rows = '<tr><td colspan="6">No audit records yet.</td></tr>'; }

        return Response::html($this->layout->render('Audit Log', '<table><thead><tr><th>Time</th><th>Actor</th><th>Action</th><th>Entity</th><th>ID</th><th>Summary</th></tr></thead><tbody>' . $rows . '</tbody></table>', $user, 'audit-log'));
    }

    private function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}
