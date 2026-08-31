<?php

declare(strict_types=1);

namespace Zoosper\Site\Admin\Lifecycle;

use Throwable;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Site\Lifecycle\SiteLifecycleCoordinator;
use Zoosper\Site\Lifecycle\SiteLifecycleResult;
use Zoosper\Site\Model\Site;

final readonly class SiteLifecycleAdminResponder
{
    public function __construct(private SiteLifecycleCoordinator $lifecycle, private CsrfTokenManager $csrf, private ?FlashMessageStoreInterface $flash = null, private ?AdminUrlGenerator $urls = null) {}

    public function actionsHtml(Site $site): string
    {
        $token = $this->e($this->csrf->token()); $id = $site->id;
        if ($site->status === 'inactive') {
            return '<section class="card site-lifecycle-actions"><h2>Site lifecycle</h2><p class="muted">Restore this Site, or permanently delete it only after all Domains, Pages, assignments, Menus, and URL Rewrites are removed.</p>'
                . $this->form("sites/{$id}/restore", $token, 'Restore Site', 'button secondary')
                . $this->form("sites/{$id}/delete", $token, 'Delete permanently', 'button button--danger', true) . '</section>';
        }
        return '<section class="card site-lifecycle-actions"><h2>Site lifecycle</h2><p class="muted">Making a Site inactive preserves configuration and content while removing it from active Site lookup.</p>'
            . $this->form("sites/{$id}/disable", $token, 'Make inactive', 'button secondary') . '</section>';
    }

    public function disable(Site $site, AdminUser $actor): Response { return $this->operate('disable', $site, $actor); }
    public function restore(Site $site, AdminUser $actor): Response { return $this->operate('restore', $site, $actor); }
    public function delete(Site $site, AdminUser $actor): Response { return $this->operate('delete', $site, $actor); }

    private function operate(string $operation, Site $site, AdminUser $actor): Response
    {
        try {
            $result = match ($operation) {
                'disable' => $this->lifecycle->disable($site, $actor->id, $actor->email),
                'restore' => $this->lifecycle->restore($site, $actor->id, $actor->email),
                'delete' => $this->lifecycle->deletePermanently($site, $actor->id, $actor->email),
                default => throw new \LogicException('Unsupported Site lifecycle operation.'),
            };
            $this->present($result);
        } catch (Throwable $exception) {
            $this->flash?->error($exception->getMessage(), 'site.lifecycle.failed');
            return Response::redirect($this->url("sites/{$site->id}/edit"), 303);
        }
        return Response::redirect($operation === 'delete' && $result->successful ? $this->url('sites') : $this->url("sites/{$site->id}/edit"), 303);
    }

    private function present(SiteLifecycleResult $result): void
    {
        if ($result->successful) { $this->flash?->success(match ($result->operation) { 'disable' => 'Site is now inactive.', 'restore' => 'Site restored to active.', 'delete' => 'Site permanently deleted.', default => 'Site lifecycle operation completed.' }, 'site.lifecycle.' . $result->operation); return; }
        $details=[]; foreach($result->blockers as $type=>$count){ if($type !== 'status' && $count>0){$details[]=$count.' '.str_replace('_',' ',$type).' reference(s)';} }
        $message=$result->message ?? 'Site lifecycle operation was blocked.'; if($details!==[]){$message.=' Blocking references: '.implode(', ',$details).'.';}
        $this->flash?->error($message, 'site.lifecycle.blocked');
    }

    private function form(string $path,string $token,string $label,string $class,bool $destructive=false): string
    {
        $description=$destructive?'<p class="muted">This cannot be undone. Database cascades are not used as an Admin content-deletion shortcut.</p>':'';
        return '<form method="post" action="'.$this->e($this->url($path)).'" class="site-lifecycle-form"><input type="hidden" name="_csrf_token" value="'.$token.'">'.$description.'<button type="submit" class="'.$this->e($class).'">'.$this->e($label).'</button></form>';
    }
    private function url(string $path): string{return $this->urls?->url($path) ?? '/admin/'.ltrim($path,'/');}
    private function e(string $value): string{return htmlspecialchars($value,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
}










