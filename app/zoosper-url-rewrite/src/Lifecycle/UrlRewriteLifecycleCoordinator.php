<?php
declare(strict_types=1);
namespace Zoosper\UrlRewrite\Lifecycle;

use InvalidArgumentException;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\UrlRewrite\Model\UrlRewrite;
use Zoosper\UrlRewrite\Repository\UrlRewriteRepository;
use Zoosper\UrlRewrite\Service\RedirectChainInspector;
use Zoosper\UrlRewrite\Service\RedirectPolicy;

final readonly class UrlRewriteLifecycleCoordinator
{
    public function __construct(private UrlRewriteRepository $rewrites, private ?AuditLoggerInterface $audit = null, private ?RedirectChainInspector $chains = null) {}

    public function disable(UrlRewrite $r, int $actorId, string $email): UrlRewriteLifecycleResult
    {
        return $this->status($r, false, $actorId, $email);
    }

    public function restore(UrlRewrite $r, int $actorId, string $email): UrlRewriteLifecycleResult
    {
        return $this->status($r, true, $actorId, $email);
    }

    public function deletePermanently(UrlRewrite $r, int $actorId, string $email): UrlRewriteLifecycleResult
    {
        if ($r->isActive) {
            return new UrlRewriteLifecycleResult(false, 'delete', $r->id, true, ['status' => 1], 'Disable the URL Rewrite before permanent deletion.');
        }
        if (!$this->rewrites->deleteIfInactive($r->id, $r->siteId)) {
            return new UrlRewriteLifecycleResult(false, 'delete', $r->id, null, message: 'URL Rewrite no longer exists or is not disabled.');
        }
        $this->log($actorId, $email, 'url_rewrite.deleted_permanently', $r, null);
        return new UrlRewriteLifecycleResult(true, 'delete', $r->id, null);
    }

    private function status(UrlRewrite $r, bool $active, int $actorId, string $email): UrlRewriteLifecycleResult
    {
        $operation = $active ? 'restore' : 'disable';
        if ($this->rewrites->findByIdForSite($r->id, $r->siteId) != $r) {
            return new UrlRewriteLifecycleResult(false, $operation, $r->id, null, message: 'URL Rewrite no longer exists or has changed.');
        }
        if ($r->isActive === $active) {
            return new UrlRewriteLifecycleResult(true, $operation, $r->id, $active, message: 'URL Rewrite already has the requested status.');
        }
        if ($active) {
            try {
                $redirect = (new RedirectPolicy())->validate($r->requestPath, $r->targetPath, $r->redirectType);
                ($this->chains ?? new RedirectChainInspector($this->rewrites))->inspect($r->siteId, $redirect['source'], $redirect['target']);
            } catch (InvalidArgumentException $exception) {
                return new UrlRewriteLifecycleResult(false, 'restore', $r->id, false, message: $exception->getMessage());
            }
        }
        if (!$this->rewrites->changeStatusIfUnchanged($r, $active)) {
            return new UrlRewriteLifecycleResult(false, $operation, $r->id, null, message: 'URL Rewrite no longer exists or has changed.');
        }
        $this->log($actorId, $email, $active ? 'url_rewrite.restored' : 'url_rewrite.disabled', $r, $active);
        return new UrlRewriteLifecycleResult(true, $active ? 'restore' : 'disable', $r->id, $active);
    }

    private function log(int $id, string $email, string $action, UrlRewrite $r, ?bool $active): void
    {
        $this->audit?->logAction($id, $email, $action, 'url_rewrite', (string) $r->id, $action, ['site_id' => $r->siteId, 'request_path' => $r->requestPath, 'active' => $active]);
    }
}










