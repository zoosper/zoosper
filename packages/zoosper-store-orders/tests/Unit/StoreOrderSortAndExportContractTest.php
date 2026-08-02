<?php

declare(strict_types=1);
namespace Zoosper\StoreOrders\Tests\Unit;
use InvalidArgumentException;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\StoreOrders\Api\StoreOrderRequestMapper;
use Zoosper\StoreOrders\StoreOrderCapabilities;
it('advertises and maps only approved remote sort fields', function (): void {
    expect(StoreOrderCapabilities::currentApi()->sortableColumns)->toBe(['order_id','order_date','customer_name','status','payment_type','total_paid','picked_up_at']);
    $context=new ApiGridContext(1,scope:['store_code'=>49,'kiosk_website_id'=>55]);
    $request=(new StoreOrderRequestMapper())->map(new GridQuery(sort:'order_date',direction:'desc'),$context);
    expect($request->query)->toHaveKey('sort','order_date')->toHaveKey('dir','desc');
    expect(fn()=>(new StoreOrderRequestMapper())->map(new GridQuery(sort:'private_payload'),$context))->toThrow(InvalidArgumentException::class,'Unsupported Store Orders sort field');
});
it('describes current-page export without enabling full remote export', function (): void {
    $root=dirname(__DIR__,4);$source=file_get_contents($root.'/docs/architecture/store-orders-grid-closure.md');
    expect($source)->not->toBeFalse()->and($source)->toContain('current visible page')->and($source)->toContain('mapped safe Grid rows');
});
