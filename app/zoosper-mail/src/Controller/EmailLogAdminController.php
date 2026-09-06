<?php

declare(strict_types=1);

namespace Zoosper\Mail\Controller;

use PDO;
use RuntimeException;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\AdminGrid\AdminCollectionGrid;
use Zoosper\AdminGrid\AdminCollectionGridQuery;
use Zoosper\Mail\Admin\EmailLogGrid;
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
    public function __construct(
        private SessionGuard $guard,
        private AdminLayout $layout,
        private EmailLogRepository $logs,
        private ?AdminUrlGenerator $adminUrls = null,
        private ?AdminCollectionGrid $collectionGrid = null,
        private ?PDO $pdo = null,
    )
    {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();
        if ($this->collectionGrid === null || $this->pdo === null) {
            throw new RuntimeException('Email Logs Admin Grid services are unavailable.');
        }
        $grid = new EmailLogGrid($this->pdo, $this->adminUrls);
        $definition = $grid->definition();
        $rendered = $this->collectionGrid->render(
            $user->id,
            EmailLogGrid::KEY,
            $this->adminUrls?->url('mail-logs') ?? '/admin/mail-logs',
            $definition,
            $grid,
            AdminCollectionGridQuery::values($request, $definition),
            AdminCollectionGridQuery::bookmark($request),
        );
        $notice = '<div class="notice notice-info email-logs-index__notice">Status sent means the configured SMTP endpoint accepted the message. It does not guarantee recipient inbox delivery.</div>';
        return Response::html($this->layout->render('Email Logs', '<div class="email-logs-index" data-email-logs-page>' . $notice . $rendered['html'] . '</div>', $user, 'mail-logs', shellTitle: ''));
    }
    public function view(Request $request): Response
    {
        $user = $this->currentAdminUser();

        $id = (int) ($request->query('id') ?? 0);
        $row = $id > 0 ? $this->logs->find($id) : null;
        if ($row === null) {
            return Response::html($this->layout->render('Email Log Not Found', '<p>Email log not found.</p>', $user, 'mail-logs'), 404);
        }

        $html = '<div class="toolbar"><a class="button secondary" href="' . $this->e($this->adminUrls?->url('mail-logs') ?? '/admin/mail-logs') . '">Back</a></div>'
            . '<div class="notice notice-info">This log proves the configured SMTP endpoint accepted or rejected the message. It does not prove recipient inbox delivery.</div>'
            . '<div class="card mail-log-detail"><h2>' . $this->e((string) $row['subject']) . '</h2>'
            . '<p><strong>Status:</strong> ' . $this->badge((string) $row['status']) . '</p>'
            . '<p><strong>From:</strong> ' . $this->e((string) $row['from_email']) . '</p>'
            . '<p><strong>To:</strong> ' . $this->e((string) $row['to_emails']) . '</p>'
            . '<p><strong>Created:</strong> ' . $this->e((string) $row['created_at']) . '</p>'
            . '<p><strong>Accepted by SMTP:</strong> ' . $this->e((string) ($row['sent_at'] ?? '')) . '</p>'
            . '<p><strong>Failed:</strong> ' . $this->e((string) ($row['failed_at'] ?? '')) . '</p>'
            . '<p><strong>Error:</strong> ' . $this->e(trim((string) ($row['error_class'] ?? '') . ' ' . (string) ($row['error_message'] ?? ''))) . '</p>'
            . '<h3>Text body</h3><pre class="mail-log-body">' . $this->e((string) ($row['text_body'] ?? '')) . '</pre>'
            . '<h3>HTML body</h3><pre class="mail-log-body">' . $this->e((string) ($row['html_body'] ?? '')) . '</pre></div>';

        return Response::html($this->layout->render('SMTP Email Log', $html, $user, 'mail-logs'));
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










