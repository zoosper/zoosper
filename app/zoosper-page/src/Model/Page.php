<?php

declare(strict_types=1);

namespace Zoosper\Page\Model;

/**
 * CMS page model.
 *
 * The `content` property remains the current sanitised HTML fallback used by
 * existing frontend rendering. `contentFormat` and `contentJson` prepare the
 * model for future block_json persistence without breaking current HTML pages.
 */
final readonly class Page
{
    public function __construct(
        public int $id,
        public int $siteId,
        public string $title,
        public string $slug,
        public string $content,
        public string $status,
        public ?int $createdBy = null,
        public ?int $updatedBy = null,
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
        public ?string $publishedAt = null,
        public string $contentFormat = 'html',
        public ?string $contentJson = null,
    ) {
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function hasBlockJson(): bool
    {
        return $this->contentFormat === 'block_json' && $this->contentJson !== null && trim($this->contentJson) !== '';
    }
}
