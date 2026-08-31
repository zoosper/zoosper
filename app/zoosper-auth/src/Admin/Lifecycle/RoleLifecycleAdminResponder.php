<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Lifecycle;

use Zoosper\Auth\Lifecycle\RoleLifecycleCoordinator;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;

final readonly class RoleLifecycleAdminResponder
{
    public function __construct(private RoleLifecycleCoordinator $lifecycle, private CsrfTokenManager $csrf, private ?FlashMessageStoreInterface $flash = null, private ?AdminUrlGenerator $urls = null)
    {
    }

    public function actionsHtml(int $roleId, string $roleCode): string
    {
        if ($roleCode === 'super_admin') {
            return '<section class="card"><h3>Role lifecycle</h3><p class="muted">The system super_admin Role cannot be deleted.</p></section>';
        }
        $count = $this->lifecycle->assignmentCount($roleId);
        $note = $count > 0 ? "This Role is assigned to {$count} Admin User(s). Remove those assignments before deletion." : 'Permanent deletion removes this unassigned custom Role and its permission links.';
        return '<section class="card"><h3>Role lifecycle</h3><p class="muted">'.$this->e($note).'</p><form method="post" action="'.$this->e($this->url("roles/{$roleId}/delete")).'"><input type="hidden" name="_csrf_token" value="'.$this->e($this->csrf->token()).'"><button type="submit" class="button button--danger"'.($count > 0 ? ' disabled' : '').'>Delete permanently</button></form></section>';
    }

    public function delete(int $roleId, string $roleCode, AdminUser $actor): Response
    {
        $result = $this->lifecycle->deletePermanently($roleId, $roleCode, $actor);
        $result->successful ? $this->flash?->success($result->message, 'admin.role.lifecycle') : $this->flash?->error($result->message, 'admin.role.lifecycle');
        return Response::redirect($result->successful ? $this->url('roles') : $this->url("roles/edit?id={$roleId}"), 303);
    }
    private function url(string $path): string { return $this->urls?->url($path) ?? '/admin/'.ltrim($path, '/'); }
    private function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
}










