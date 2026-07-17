<?php

declare(strict_types=1);

namespace Zoosper\Mail\Controller;

use RuntimeException;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Mail\Log\EmailLogRepository;

/**
 * Admin controller for searchable outbound SMTP email logs.
 *
 * The grid can show message content for diagnostics. The `sent` status means the
 * configured SMTP endpoint accepted the message. It does not guarantee the
 * recipient saw the email. This log must not be used to expose OTPs, TOTP
 * secrets, recovery-code plaintext, provisioning URIs, reset tokens, SMTP
 * passwords or payment data.
 */
final readonly class EmailLogAdminController
{
    public function __construct(private SessionGuard $guard, private AdminLayout $layout, private EmailLogRepository $logs)
    {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $filters = [
            'status' => (string) ($request->query('status') ?? ''),
            'email' => (string) ($request->query('email') ?? ''),
            'subject' => (string) ($request->query('subject') ?? ''),
        ];
        $rows = $this->logs->search($filters, 100);

        $html = '<div class="notice notice-info">Status <strong>sent</strong> means Zoosper handed the message to the configured SMTP endpoint. It does not guarantee recipient inbox delivery. If SMTP is Mailpit/MailHog, view the message in the local catcher.</div>';
        $html .= $this->filters($filters) . '<table><thead><tr><th>ID</th><th>Status</th><th>To</th><th>Subject</th><th>Created</th><th>Actions</th></tr></thead><tbody>';
        foreach ($rows as $row) {
            $html .= '<tr><td>' . (int) $row['id'] . '</td><td>' . $this->badge((string) $row['status']) . '</td><td>' . $this->e((string) $row['to_emails']) . '</td><td>' . $this->e((string) $row['subject']) . '</td><td>' . $this->e((string) $row['created_at']) . '</td><td><a href="/admin/mail-logs/view?id=' . (int) $row['id'] . '">View</a></td></tr>';
        }
        if ($rows === []) {
            $html .= '<tr><td colspan="6">No email logs found.</td></tr>';
        }
        $html .= '</tbody></table>';

        return Response::html($this->layout->render('SMTP Email Logs', $html, $user, 'mail-logs'));
    }

    public function view(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $id = (int) ($request->query('id') ?? 0);
        $row = $id > 0 ? $this->logs->find($id) : null;
        if ($row === null) {
            return Response::html($this->layout->render('Email Log Not Found', '<p>Email log not found.</p>', $user, 'mail-logs'), 404);
        }

        $html = '<div class="toolbar"><a class="button secondary" href="/admin/mail-logs">Back</a></div>'
            . '<div class="notice notice-info">This log proves the configured SMTP endpoint accepted or rejected the message. It does not prove recipient inbox delivery.</div>'
            . '<div class="card"><h2>' . $this->e((string) $row['subject']) . '</h2>'
            . '<p><strong>Status:</strong> ' . $this->badge((string) $row['status']) . '</p>'
            . '<p><strong>From:</strong> ' . $this->e((string) $row['from_email']) . '</p>'
            . '<p><strong>To:</strong> ' . $this->e((string) $row['to_emails']) . '</p>'
            . '<p><strong>Created:</strong> ' . $this->e((string) $row['created_at']) . '</p>'
            . '<p><strong>Accepted by SMTP:</strong> ' . $this->e((string) ($row['sent_at'] ?? '')) . '</p>'
            . '<p><strong>Failed:</strong> ' . $this->e((string) ($row['failed_at'] ?? '')) . '</p>'
            . '<p><strong>Error:</strong> ' . $this->e(trim((string) ($row['error_class'] ?? '') . ' ' . (string) ($row['error_message'] ?? ''))) . '</p>'
            . '<h3>Text body</h3><pre>' . $this->e((string) ($row['text_body'] ?? '')) . '</pre>'
            . '<h3>HTML body</h3><pre>' . $this->e((string) ($row['html_body'] ?? '')) . '</pre></div>';

        return Response::html($this->layout->render('SMTP Email Log', $html, $user, 'mail-logs'));
    }

    private function filters(array $filters): string
    {
        return '<form method="get" action="/admin/mail-logs" class="toolbar">'
            . '<label>Status <select name="status"><option value="">Any</option>'
            . $this->option('sent', $filters['status'])
            . $this->option('failed', $filters['status'])
            . '</select></label>'
            . '<label>Email <input type="text" name="email" value="' . $this->e($filters['email']) . '"></label>'
            . '<label>Subject <input type="text" name="subject" value="' . $this->e($filters['subject']) . '"></label>'
            . '<button type="submit">Search</button></form>';
    }

    private function option(string $value, string $selected): string
    {
        return '<option value="' . $this->e($value) . '"' . ($value === $selected ? ' selected' : '') . '>' . $this->e(ucfirst($value)) . '</option>';
    }

    private function badge(string $status): string
    {
        return '<span class="badge badge-' . $this->e($status) . '">' . $this->e($status) . '</span>';
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
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
