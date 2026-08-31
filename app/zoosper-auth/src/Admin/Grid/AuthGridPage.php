<?php

declare(strict_types=1);

namespace Zoosper\Auth\Admin\Grid;

use Zoosper\AdminGrid\GridViewState;
use Zoosper\Pagination\PaginationResult;

/** Complete, framework-neutral view model for an Auth admin Grid page. */
final readonly class AuthGridPage
{
    /** @param PaginationResult<array<string, mixed>> $pagination */
    public function __construct(
        public string $title,
        public string $workspaceHtml,
        public string $gridHtml,
        public GridViewState $state,
        public PaginationResult $pagination,
    ) {
        if ($title === '') {
            throw new \InvalidArgumentException('Auth Grid page title cannot be empty.');
        }
    }
}










