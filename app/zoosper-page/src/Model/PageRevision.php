<?php

declare(strict_types=1);
namespace Zoosper\Page\Model;
final readonly class PageRevision
{
    public function __construct(public int $id, public int $pageId, public string $title, public string $slug, public string $content, public string $status, public string $contentFormat, public ?string $contentJson, public ?string $metaTitle, public ?string $metaDescription, public ?string $metaKeywords, public ?string $canonicalUrl, public ?int $createdBy, public string $createdAt) {}
}
