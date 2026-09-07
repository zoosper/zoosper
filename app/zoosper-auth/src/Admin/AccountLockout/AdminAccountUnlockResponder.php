<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\AccountLockout;

use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Auth\AccountLockout\AdminAccountLockoutService;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Url\AdminUrlGenerator;

/** Protected Admin presentation and mutation boundary for account lockout state. */
final readonly class AdminAccountUnlockResponder
{
    public function __construct(
        private AdminAccountLockoutService $lockouts,
        private CsrfTokenManager $csrf,
        private ?FlashMessageStoreInterface $flash = null,
        private ?AdminUrlGenerator $urls = null,
        private ?AuditLoggerInterface $audit = null,
    ) {
    }

    public function actionsHtml(AdminUser $target): string
    {
        $state = $this->lockouts->state($target->id);
        if ($state === null) {
            return '';
        }
        $locked = $state->isLockedAt(time());
        $heading = $locked ? 'Account temporarily locked' : 'Failed sign-in attempts';
        $until = $locked && $state->lockedUntil !== null
            ? '<p><strong>Locked until:</strong> <time datetime="' . $this->e(str_replace(' ', 'T', $state->lockedUntil) . 'Z') . '">' . $this->e($state->lockedUntil) . ' UTC</time></p>'
            : '';
        return '<section class="card admin-account-lockout" aria-labelledby="admin-account-lockout-heading">'
            . '<h3 id="admin-account-lockout-heading">' . $this->e($heading) . '</h3>'
            . '<p><strong>Failed password attempts:</strong> ' . $state->failedAttempts . '</p>' . $until
            . '<form method="post" action="' . $this->e($this->url('users/' . $target->id . '/unlock')) . '">'
            . '<input type="hidden" name="_csrf_token" value="' . $this->e($this->csrf->token()) . '">'
            . '<button type="submit" class="button secondary">' . ($locked ? 'Unlock account' : 'Clear failed attempts') . '</button>'
            . '</form></section>';
    }

    public function unlock(AdminUser $target, AdminUser $actor): Response
    {
        $hadState = $this->lockouts->state($target->id) !== null;
        $this->lockouts->clear($target->id);
        if ($hadState) {
            $this->flash?->success('Admin account sign-in lockout cleared.', 'admin.user.account-unlocked');
            $this->audit?->logAction($actor->id, $actor->email, 'admin_user.account_unlocked');
        } else {
            $this->flash?->success('The Admin account has no active lockout.', 'admin.user.account-unlocked');
        }
        return Response::redirect($this->url('users/edit?id=' . $target->id), 303);
    }

    private function url(string $path): string
    {
        return $this->urls?->url($path) ?? '/admin/' . ltrim($path, '/');
    }

    private function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
