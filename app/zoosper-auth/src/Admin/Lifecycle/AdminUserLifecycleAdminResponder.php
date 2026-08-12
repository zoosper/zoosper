<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Lifecycle;

use Zoosper\Auth\Lifecycle\AdminUserLifecycleCoordinator;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;

final readonly class AdminUserLifecycleAdminResponder
{
    public function __construct(private AdminUserLifecycleCoordinator $lifecycle, private CsrfTokenManager $csrf, private ?FlashMessageStoreInterface $flash = null, private ?AdminUrlGenerator $urls = null)
    {
    }

    public function actionsHtml(AdminUser $target, AdminUser $actor): string
    {
        if ($target->id === $actor->id) {
            return '<section class="card"><h3>Admin User lifecycle</h3><p class="muted">The currently authenticated account cannot be disabled.</p></section>';
        }
        $action = $target->status === 'inactive' ? 'restore' : 'disable';
        $label = $target->status === 'inactive' ? 'Restore Admin User' : 'Make inactive';
        $description = $target->status === 'inactive' ? 'Restore sign-in access without replacing this identity or its audit history.' : 'Making an Admin User inactive blocks sign-in while preserving roles, audit history and ownership references.';
        return '<section class="card"><h3>Admin User lifecycle</h3><p class="muted">'.$this->e($description).'</p><form method="post" action="'.$this->e($this->url("users/{$target->id}/{$action}")).'"><input type="hidden" name="_csrf_token" value="'.$this->e($this->csrf->token()).'"><button type="submit" class="button secondary">'.$this->e($label).'</button></form></section>';
    }

    public function disable(AdminUser $target, AdminUser $actor): Response { return $this->respond($this->lifecycle->disable($target, $actor), $target->id); }
    public function restore(AdminUser $target, AdminUser $actor): Response { return $this->respond($this->lifecycle->restore($target, $actor), $target->id); }

    private function respond(\Zoosper\Auth\Lifecycle\AdminUserLifecycleResult $result, int $id): Response
    {
        $result->successful ? $this->flash?->success($result->message, 'admin.user.lifecycle') : $this->flash?->error($result->message, 'admin.user.lifecycle');
        return Response::redirect($this->url("users/edit?id={$id}"), 303);
    }
    private function url(string $path): string { return $this->urls?->url($path) ?? '/admin/'.ltrim($path, '/'); }
    private function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}
