<?php

declare(strict_types=1);
namespace Zoosper\Page\Service;
use RuntimeException;
use Zoosper\Page\Model\PageRevision;
use Zoosper\Page\Repository\PageRevisionRepository;
final readonly class PageRevisionService
{
    public function __construct(private PageRevisionRepository $revisions, private int $retention = 50) {}
    /** @param array<string,mixed> $snapshot */
    public function capture(int $pageId, array $snapshot, ?int $actorId): int
    {
        foreach(['title','slug','content','status'] as $required){if(!isset($snapshot[$required])){throw new RuntimeException("Revision snapshot requires {$required}.");}}
        $id=$this->revisions->capture($pageId,$snapshot,$actorId); $this->revisions->prune($pageId,$this->retention); return $id;
    }
    /** @return list<PageRevision> */ public function history(int $pageId): array{return $this->revisions->forPage($pageId,$this->retention);}
    public function revision(int $pageId,int $revisionId): PageRevision{return $this->revisions->findForPage($revisionId,$pageId) ?? throw new RuntimeException('Page revision was not found for this page.');}
}
