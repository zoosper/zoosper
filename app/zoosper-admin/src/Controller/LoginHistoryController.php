<?php

declare(strict_types=1);

namespace Zoosper\Admin\Controller;

use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class LoginHistoryController
{
    public function __construct(private SessionGuard $guard, private LoginHistoryRepository $history, private AdminLayout $layout)
    {
    }

    public function index(Request $request): Response
    {
        $user = $this->guard->requirePermission('role.manage');
        if ($user === null) {
            return Response::redirect('/admin/login');
        }

        $rows = '';
        foreach ($this->history->latest() as $row) {
            $rows .= '<tr><td>' . $this->e((string) $row['created_at']) . '</td><td>' . $this->e((string) $row['email']) . '</td><td><code>' . $this->e((string) $row['status']) . '</code></td><td>' . $this->e((string) $row['ip_address']) . '</td></tr>';
        }
        if ($rows === '') { $rows = '<tr><td colspan="4">No login history yet.</td></tr>'; }

        return Response::html($this->layout->render('Login History', '<table><thead><tr><th>Time</th><th>Email</th><th>Status</th><th>IP</th></tr></thead><tbody>' . $rows . '</tbody></table>', $user, 'login-history'));
    }

    private function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}
