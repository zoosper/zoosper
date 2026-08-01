<?php

declare(strict_types=1);

namespace Zoosper\AdminGrid;

/** Immutable CSV response payload for a feature-owned export controller. */
final readonly class GridWorkspaceExportResult
{
    public function __construct(
        public string $filename,
        public string $csv,
        public int $exportedRows,
        public bool $truncated,
    ) {
        if (!preg_match('/^[a-z0-9][a-z0-9._-]*\.csv$/', $this->filename)) {
            throw new \InvalidArgumentException('Grid export filename is not safe.');
        }
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $this->filename . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ];
    }
}
