<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Immutable navigation links for one resolved Grid workspace. */
final readonly class GridWorkspaceNavigation
{
    /** @param array<string, string> $sortUrls */
    public function __construct(
        public ?string $previousUrl,
        public ?string $nextUrl,
        public array $sortUrls,
        public string $exportUrl,
    ) {
        foreach (array_filter([
            $this->previousUrl,
            $this->nextUrl,
            $this->exportUrl,
            ...array_values($this->sortUrls),
        ]) as $url) {
            if ($url[0] !== '/' || str_contains($url, '://')) {
                throw new \InvalidArgumentException(
                    'Grid navigation URLs must use application-local paths.',
                );
            }
        }
    }
}











