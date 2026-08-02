<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Mapping;

use Zoosper\ApiGrid\Transport\ApiResponse;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\Grid\DataSource\GridResult;

interface ApiGridResponseMapperInterface
{
    public function map(ApiResponse $response, GridQuery $query): GridResult;
}
