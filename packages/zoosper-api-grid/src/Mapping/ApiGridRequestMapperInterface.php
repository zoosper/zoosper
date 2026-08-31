<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Mapping;

use Zoosper\ApiGrid\Transport\ApiRequest;
use Zoosper\Grid\DataSource\GridQuery;

interface ApiGridRequestMapperInterface
{
    public function map(GridQuery $query, ApiGridContext $context): ApiRequest;
}











