<?php

declare(strict_types=1);

namespace Zoosper\Page\Admin\BulkAction;

use Zoosper\Grid\BulkAction\GridBulkExecutionContext;
use Zoosper\Page\Model\Page;

/** Publishes the established Page event and audit side effects after persistence. */
interface PagePublishSideEffectsInterface
{
    public function afterPublished(
        Page $page,
        GridBulkExecutionContext $context,
        int $selectedCount,
    ): void;
}
