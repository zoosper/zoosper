<?php

declare(strict_types=1);

namespace Zoosper\Media\Repository;

use Zoosper\Pagination\Pager;

/** Allow-listed filters and ordering for Media collection reads. */
final readonly class MediaAssetCriteria
{
    /** @var list<string> */
    public const SORTABLE_FIELDS = [
        'id',
        'original_filename',
        'mime_type',
        'extension',
        'size_bytes',
        'status',
        'created_at',
        'updated_at',
    ];

    public function __construct(
        public Pager $pager,
        public ?string $query = null,
        public ?string $status = null,
        public ?string $mimeType = null,
        public ?string $extension = null,
        public string $sortBy = 'created_at',
        public string $sortDirection = 'desc',
    ) {
        if (!in_array($this->sortBy, self::SORTABLE_FIELDS, true)) {
            throw new \InvalidArgumentException('Unsupported Media sort field.');
        }
        if (!in_array($this->sortDirection, ['asc', 'desc'], true)) {
            throw new \InvalidArgumentException('Unsupported Media sort direction.');
        }
        if ($this->status !== null && !in_array($this->status, ['active', 'archived'], true)) {
            throw new \InvalidArgumentException('Unsupported Media status filter.');
        }
    }
}











