<?php

declare(strict_types=1);

namespace Zoosper\Page\Event;

/** Emitted after an admin user publishes a page. */
final readonly class PagePublishedEvent
{
    public function __construct(public int $pageId, public int $adminUserId)
    {
    }
}
