<?php
declare(strict_types=1);
namespace Zoosper\Examples\ApiGrid\Api;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Mapping\ApiGridRequestMapperInterface;
use Zoosper\ApiGrid\Transport\ApiRequest;
use Zoosper\Grid\DataSource\GridQuery;
final class ExampleRequestMapper implements ApiGridRequestMapperInterface
{
    #[\Override]
    public function map(GridQuery $query, ApiGridContext $context): ApiRequest
    {
        return new ApiRequest('GET', '/records', ['page'=>$query->page, 'per_page'=>$query->pageSize, 'scope_id'=>$context->requireInt('scope_id')]);
    }
}
