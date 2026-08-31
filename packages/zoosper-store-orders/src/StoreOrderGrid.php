<?php

declare(strict_types=1);

namespace Zoosper\StoreOrders;

use Zoosper\ApiGrid\Definition\ApiGridDefinition;
use Zoosper\Grid\GridColumn;
use Zoosper\Grid\GridDefinition;
use Zoosper\Grid\GridFilter;

final class StoreOrderGrid
{
    public static function definition(): ApiGridDefinition
    {
        return new ApiGridDefinition(
            key: 'store.orders',
            title: 'Store Orders',
            route: '/admin/store-orders',
            permission: 'store_order.view',
            dataSourceService: 'store_orders.data_source',
            grid: new GridDefinition(
                title: 'Store Orders',
                columns: [
                    new GridColumn('order_id', 'Order #', false),
                    new GridColumn('order_date', 'Placed', false),
                    new GridColumn('customer_name', 'Customer', false),
                    new GridColumn('status', 'Status', false),
                    new GridColumn('payment_type', 'Payment', false),
                    new GridColumn('total_paid', 'Total', false),
                    new GridColumn('picked_up_at', 'Picked up', false),
                    new GridColumn('actions', 'Actions', false),
                ],
                filters: [
                    new GridFilter('store_code', 'Store Code'),
                    new GridFilter('kiosk_website_id', 'Kiosk Website ID'),
                    new GridFilter('order_id', 'Order Number'),
                    new GridFilter('customer', 'Customer'),
                    new GridFilter('status', 'Status'),
                    new GridFilter('placed_from', 'Placed From', 'date'),
                    new GridFilter('placed_to', 'Placed To', 'date'),
                ],
                defaultSort: 'order_date',
                defaultSortDir: 'desc',
            ),
            pageSizes: [5, 10, 20, 50, 100],
            exportPermission: 'store_order.export',
        );
    }
}











