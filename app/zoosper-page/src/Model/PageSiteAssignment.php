<?php

declare(strict_types=1);

namespace Zoosper\Page\Model;

/**
 * Immutable relationship between a CMS page and a site/store view.
 *
 * Page-site assignments allow one content page to be visible on multiple sites
 * or store views without duplicating the page record. Future admin screens can
 * expose this as a multi-select field on the page edit form.
 */
final readonly class PageSiteAssignment
{
    public function __construct(
        public int $id,
        public int $pageId,
        public int $siteId,
    ) {
    }
}
