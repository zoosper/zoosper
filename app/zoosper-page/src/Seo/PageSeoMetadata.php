<?php

declare(strict_types=1);

namespace Zoosper\Page\Seo;

/** Engine-neutral, already-normalised metadata passed to frontend layouts. */
final readonly class PageSeoMetadata
{
    public function __construct(
        public string $title,
        public ?string $description,
        public ?string $canonicalUrl,
        public string $robots,
        public string $openGraphTitle,
        public ?string $openGraphDescription,
        public ?string $openGraphUrl,
    ) {
    }

    /** @return array<string, string|null> */
    public function toLayoutData(): array
    {
        return [
            'title' => $this->title,
            'metaDescription' => $this->description,
            'canonicalUrl' => $this->canonicalUrl,
            'robots' => $this->robots,
            'openGraphTitle' => $this->openGraphTitle,
            'openGraphDescription' => $this->openGraphDescription,
            'openGraphUrl' => $this->openGraphUrl,
        ];
    }
}
