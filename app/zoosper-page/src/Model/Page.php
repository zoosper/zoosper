<?php

declare(strict_types=1);

namespace Zoosper\Page\Model;

final readonly class Page
{
    public function __construct(
        public int $id,
        public int $siteId,
        public string $title,
        public string $slug,
        public string $content,
        public string $status,
        public ?string $metaTitle,
        public ?string $metaDescription,
        public ?string $publishedAt,
    ) {
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }
}
