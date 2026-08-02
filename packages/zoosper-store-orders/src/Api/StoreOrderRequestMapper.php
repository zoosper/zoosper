<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders\Api;

use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Mapping\ApiGridRequestMapperInterface;
use Zoosper\ApiGrid\Transport\ApiRequest;
use Zoosper\Grid\DataSource\GridQuery;

final class StoreOrderRequestMapper implements ApiGridRequestMapperInterface
{
    public function map(GridQuery $query, ApiGridContext $context): ApiRequest
    {
        return new ApiRequest(
            method: 'GET',
            endpoint: '/v3/orders/store',
            query: [
                'page' => $query->page,
                'per_page' => $query->pageSize,
                'store_code' => $context->requireInt('store_code'),
                'kiosk_website_id' => $context->requireInt('kiosk_website_id'),
            ],
        );
    }
}
