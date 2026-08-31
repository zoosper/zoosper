<?php
declare(strict_types=1);
namespace Zoosper\Audit\Admin\Grid;
use Zoosper\AdminGrid\GridViewState;
use Zoosper\Pagination\PaginationResult;
final readonly class OperationalGridPage
{
    /** @param PaginationResult<array<string,mixed>> $pagination */
    public function __construct(public string $title,public string $workspaceHtml,public string $gridHtml,public GridViewState $state,public PaginationResult $pagination)
    {
        if ($title==='') throw new \InvalidArgumentException('Operational Grid title cannot be empty.');
    }
}









