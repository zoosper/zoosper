<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Api;

use UnexpectedValueException;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMapperInterface;
use Zoosper\ApiGrid\Transport\ApiResponse;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\Grid\DataSource\GridResult;

final readonly class StoreOrderResponseMapper implements ApiGridResponseMapperInterface
{
    public function __construct(private StoreOrderRowMapper $rows = new StoreOrderRowMapper())
    {
    }

    public function map(ApiResponse $response, GridQuery $query): GridResult
    {
        $records = $response->decodedBody['records'] ?? null;
        $total = $response->decodedBody['total'] ?? null;
        if (!is_array($records) || !is_int($total) || $total < 0) {
            throw new UnexpectedValueException('Store Orders response requires records[] and a non-negative integer total.');
        }

        $items = [];
        foreach ($records as $record) {
            if (!is_array($record)) {
                throw new UnexpectedValueException('Each Store Orders record must be an object.');
            }
            $items[] = $this->rows->map($record);
        }

        return new GridResult($items, $total, $query->page, $query->pageSize);
    }
}
