<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\AdminGrid\GridWorkspaceCsvExportService;
use Zoosper\AdminGrid\GridWorkspaceExportResult;

/** Pages-owned export seam using the already authorised resolved view. */
final readonly class PageGridExportCoordinator
{
    public const FILENAME = 'pages.csv';

    public function __construct(private GridWorkspaceCsvExportService $exports)
    {
    }

    /** @param iterable<array<string, mixed>> $rows */
    public function export(GridViewState $state, iterable $rows): GridWorkspaceExportResult
    {
        return $this->exports->export($state, $rows, self::FILENAME);
    }
}
