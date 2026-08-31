<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\Controller;

use Throwable;
use Zoosper\Core\Message\FlashMessageStoreInterface;
use Zoosper\AdminGrid\BulkAction\GridBulkExecutionResultAdapter;
use Zoosper\AdminGrid\BulkAction\GridBulkHostBindings;
use Zoosper\AdminGrid\BulkAction\GridBulkHttpRequest;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Core\Event\EventDispatcherInterface;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Grid\BulkAction\GridBulkActor;
use Zoosper\Grid\BulkAction\GridBulkExecutionContext;
use Zoosper\Page\Admin\BulkAction\PageBulkActionBackend;
use Zoosper\Page\Admin\PageGridWorkspace;
use Zoosper\Page\Repository\PageRepository;

/** Protected HTTP endpoint for Page-owned server bulk actions. */
final readonly class PageBulkActionController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private PageRepository $pages,
        private EventDispatcherInterface $events,
        private AuditLoggerInterface $audit,
        private FlashMessageStoreInterface $flashMessages,
        private GridBulkExecutionResultAdapter $resultAdapter = new GridBulkExecutionResultAdapter(),
        private ?AdminUrlGenerator $adminUrls = null,
    ) {
    }

    public function execute(Request $request): Response
    {
        $user = $this->guard->requirePermission('page.manage');
        if ($user === null) {
            return Response::html('Forbidden', 403);
        }

        $form = $request->form();
        $form['_csrf'] = $form['_csrf'] ?? $form['_csrf_token'] ?? '';
        $bindings = new GridBulkHostBindings(
            csrfValidator: fn (string $token): bool => $this->csrf->isValid($token),
            permissionChecker: fn (string $permission): bool => $user->can($permission),
            auditReadiness: static fn (): bool => true,
        );
        $backend = new PageBulkActionBackend($this->pages, $this->events, $this->audit, $bindings);

        try {
            $result = $backend->coordinator->execute(
                PageGridWorkspace::GRID_KEY,
                new GridBulkHttpRequest($request->method(), $form),
                new GridBulkExecutionContext(new GridBulkActor($user->id, $user->email)),
            );
            $pagesUrl = $this->adminUrls?->url('pages') ?? '/admin/pages';
            $http = $this->resultAdapter->adapt($result, $pagesUrl);
            $this->flashMessages->success($http->message, 'page.bulk_action.success');

            return Response::redirect($http->redirectPath ?? $pagesUrl, $http->status);
        } catch (Throwable $exception) {
            $this->flashMessages->error($exception->getMessage(), 'page.bulk_action.failed');

            return Response::redirect($this->adminUrls?->url('pages') ?? '/admin/pages', 303);
        }
    }
}










