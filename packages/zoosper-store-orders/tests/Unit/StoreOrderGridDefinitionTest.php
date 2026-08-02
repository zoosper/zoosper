<?php

declare(strict_types=1);
namespace Zoosper\StoreOrders\Tests\Unit;
use Zoosper\StoreOrders\StoreOrderCapabilities;
it('declares approved Store Orders sorting', function (): void { expect(StoreOrderCapabilities::currentApi()->sortableColumns)->toBe(['order_id','order_date','customer_name','status','payment_type','total_paid','picked_up_at']); });
