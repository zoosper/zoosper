<?php

declare(strict_types=1);

namespace Zoosper\Page\Event;

/** Emitted after an admin user unpublishes a page. */
final readonly class PageUnpublishedEvent
{
    public function __construct(public int $pageId, public int $adminUserId)
    {
    }
}
