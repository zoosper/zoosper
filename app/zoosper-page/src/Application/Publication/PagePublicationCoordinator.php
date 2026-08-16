<?php

declare(strict_types=1);

namespace Zoosper\Page\Application\Publication;

use Zoosper\Core\Event\EventDispatcherInterface;
use Zoosper\Page\Event\PageEvents;
use Zoosper\Page\Event\PagePublishedEvent;
use Zoosper\Page\Event\PageUnpublishedEvent;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Repository\PageRepository;

/** Application-owned single-Page publication boundary shared by Admin and API. */
final readonly class PagePublicationCoordinator
{
    public function __construct(
        private PageRepository $pages,
        private ?EventDispatcherInterface $events = null,
    ) {
    }

    public function publish(Page $page, int $adminUserId): void
    {
        $this->pages->publish($page->id, $adminUserId);
        $this->events?->dispatch(PageEvents::PUBLISHED, new PagePublishedEvent($page->id, $adminUserId));
    }

    public function unpublish(Page $page, int $adminUserId): void
    {
        $this->pages->unpublish($page->id, $adminUserId);
        $this->events?->dispatch(PageEvents::UNPUBLISHED, new PageUnpublishedEvent($page->id, $adminUserId));
    }
}
