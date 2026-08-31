<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\BulkAction;

use Zoosper\Audit\Contract\AuditLoggerInterface;
use Zoosper\Core\Event\EventDispatcherInterface;
use Zoosper\Grid\BulkAction\GridBulkExecutionContext;
use Zoosper\Page\Event\PageEvents;
use Zoosper\Page\Event\PagePublishedEvent;
use Zoosper\Page\Model\Page;

/** Required publication event and audit side effects for bulk Page publishing. */
final readonly class PagePublishSideEffects implements PagePublishSideEffectsInterface
{
    public function __construct(
        private EventDispatcherInterface $events,
        private AuditLoggerInterface $audit,
    ) {
    }

    public function afterPublished(
        Page $page,
        GridBulkExecutionContext $context,
        int $selectedCount,
    ): void {
        $actor = $context->actor;

        $this->events->dispatch(
            PageEvents::PUBLISHED,
            new PagePublishedEvent($page->id, $actor->adminUserId),
        );

        $this->audit->logAction(
            actorAdminUserId: $actor->adminUserId,
            actorEmail: $actor->email,
            action: 'page.bulk_publish',
            entityType: 'page',
            entityId: (string) $page->id,
            summary: sprintf('Published Page "%s" through the Pages bulk action.', $page->title),
            metadata: [
                'bulk_action' => PagePublishSelectedExecutor::ACTION_ID,
                'selected_count' => $selectedCount,
                'page_id' => $page->id,
                'site_id' => $page->siteId,
                'previous_status' => $page->status,
                'new_status' => 'published',
            ],
        );
    }
}










