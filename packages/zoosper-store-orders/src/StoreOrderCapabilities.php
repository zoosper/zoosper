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
            sortableColumns: [],
            filterableFields: [],
        );
    }
}
