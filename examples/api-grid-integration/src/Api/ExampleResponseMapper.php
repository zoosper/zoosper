<?php
declare(strict_types=1);
namespace Zoosper\Examples\ApiGrid\Api;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMapperInterface;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMappingException;
use Zoosper\ApiGrid\Transport\ApiResponse;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\Grid\DataSource\GridResult;
final class ExampleResponseMapper implements ApiGridResponseMapperInterface
{
    #[\Override]
    public function map(ApiResponse $response, GridQuery $query): GridResult
    {
        $records=$response->decodedBody['records']??null;
        $total=$response->decodedBody['total']??null;
        if (!is_array($records) || !is_int($total)) throw new ApiGridResponseMappingException('Example API response schema mismatch.');
        return new GridResult($records,$total,$query->page,$query->pageSize);
    }
}
