<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Page;

use Zoosper\ApiGrid\Definition\ApiGridDefinition;
use Zoosper\Grid\DataSource\GridDataSourceCapabilities;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\Grid\DataSource\GridResult;

final readonly class ApiGridPage
{
    public function __construct(
        public ApiGridDefinition $definition,
        public GridQuery $query,
        public GridResult $result,
        public GridDataSourceCapabilities $capabilities,
    ) {
    }
}
