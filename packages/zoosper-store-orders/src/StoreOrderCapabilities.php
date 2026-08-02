<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders;

use Zoosper\Grid\DataSource\GridDataSourceCapabilities;

final class StoreOrderCapabilities
{
    public static function currentApi(): GridDataSourceCapabilities
    {
        return new GridDataSourceCapabilities(
            searchable: false,
            exportable: false,
            sortableColumns: [
                'order_id',
                'order_date',
                'customer_name',
                'status',
                'payment_type',
                'total_paid',
                'picked_up_at',
            ],
            filterableFields: ['order_id', 'customer', 'status', 'placed_from', 'placed_to'],
        );
    }
}
