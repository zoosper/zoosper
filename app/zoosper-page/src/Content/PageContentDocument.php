<?php

declare(strict_types=1);

namespace Zoosper\Page\Content;

/**
 * Lightweight value object representing dual page content storage.
 *
 * Phase 0.77 does not change repository persistence yet. This value object gives
 * future repository and renderer code a safe common shape for HTML and
 * structured JSON content.
 */
final readonly class PageContentDocument
{
    public function __construct(
        public ContentFormat $format,
        public string $html,
        public ?string $json = null,
    ) {
    }

    /** @param array<string, mixed> $row */
    public static function fromRow(array $row): self
    {
        return new self(
            format: ContentFormat::fromNullable(isset($row['content_format']) ? (string) $row['content_format'] : null),
            html: (string) ($row['content'] ?? ''),
            json: isset($row['content_json']) && $row['content_json'] !== null ? (string) $row['content_json'] : null,
        );
    }

    /** @return array<string, mixed> */
    public function toRowValues(): array
    {
        return [
            'content_format' => $this->format->value,
            'content' => $this->html,
            'content_json' => $this->json,
        ];
    }
}
