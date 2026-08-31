<?php

declare(strict_types=1);

namespace Zoosper\Page\Service;

use RuntimeException;
use Zoosper\Page\Model\Page;
use Zoosper\Page\Model\PageRevision;
use Zoosper\Page\Repository\PageRepository;
use Zoosper\Page\Repository\PageRevisionRepository;

/** Owns complete Page snapshots, retention and safely scoped restoration. */
final readonly class PageRevisionService
{
    public function __construct(
        private PageRevisionRepository $revisions,
        private int $retention = 50,
    ) {
    }

    public function capturePage(Page $page, ?int $actorId): int
    {
        return $this->capture($page->id, [
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => $page->content,
            'status' => $page->status,
            'content_format' => $page->contentFormat,
            'content_json' => $page->contentJson,
            'meta_title' => $page->metaTitle,
            'meta_description' => $page->metaDescription,
            'meta_keywords' => $page->metaKeywords,
            'canonical_url' => $page->canonicalUrl,
        ], $actorId);
    }

    /** @param array<string, mixed> $snapshot */
    public function capture(int $pageId, array $snapshot, ?int $actorId): int
    {
        foreach (['title', 'slug', 'content', 'status'] as $required) {
            if (!array_key_exists($required, $snapshot)) {
                throw new RuntimeException("Revision snapshot requires {$required}.");
            }
        }
        $id = $this->revisions->capture($pageId, $snapshot, $actorId);
        $this->revisions->prune($pageId, $this->retention);
        return $id;
    }

    /** @return list<PageRevision> */
    public function history(int $pageId): array
    {
        return $this->revisions->forPage($pageId, $this->retention);
    }

    /** @return list<PageRevision> */
    public function historyPage(int $pageId, int $page, int $pageSize = 10): array
    {
        return $this->revisions->pageForPage($pageId, $page, $pageSize);
    }

    public function historyCount(int $pageId): int
    {
        return $this->revisions->countForPage($pageId);
    }

    public function revision(int $pageId, int $revisionId): PageRevision
    {
        return $this->revisions->findForPage($revisionId, $pageId)
            ?? throw new RuntimeException('Page revision was not found for this page.');
    }

    public function restore(
        Page $current,
        int $revisionId,
        int $actorId,
        PageRepository $pages,
    ): PageRevision {
        $target = $this->revision($current->id, $revisionId);
        $this->capturePage($current, $actorId);
        $pages->restoreRevision($current->siteId, $target, $actorId);
        return $target;
    }
}










