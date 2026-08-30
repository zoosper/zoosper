<?php

declare(strict_types=1);

namespace Zoosper\GlobalAnnouncements\Announcement;

use DateTimeImmutable;

final readonly class AdminAnnouncement
{
    public function __construct(
        public int $id,
        public string $title,
        public string $body,
        public string $status = 'draft',
        public ?DateTimeImmutable $publishedAt = null,
        public ?int $createdByUserId = null,
        public ?DateTimeImmutable $createdAt = null,
        public ?DateTimeImmutable $updatedAt = null,
    ) {
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }
}
