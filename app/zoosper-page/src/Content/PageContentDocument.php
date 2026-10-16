<?php

declare(strict_types=1);

namespace Zoosper\Page\Content;

/**
 * Immutable Page content value crossing application, API and rendering boundaries.
 *
 * HTML remains the compatibility and recovery representation. Structured content
 * is present only for a validated block_json document.
 *
 * @psalm-suppress PossiblyUnusedProperty HTML is the public compatibility and recovery representation.
 */
final readonly class PageContentDocument
{
    /** @param array<string, mixed>|null $structured */
    public function __construct(
        public ContentFormat $format,
        public string $html,
        public ?array $structured = null,
    ) {
    }

    /** @param array<string, mixed> $document */
    public static function structured(array $document, string $html = ''): self
    {
        return new self(ContentFormat::BlockJson, $html, $document);
    }

    public static function html(string $html): self
    {
        return new self(ContentFormat::Html, $html);
    }

    public function isStructured(): bool
    {
        return $this->format === ContentFormat::BlockJson && $this->structured !== null;
    }

    /** @return array<string, mixed>|null */
    public function toApiValue(): ?array
    {
        return $this->structured;
    }
}
