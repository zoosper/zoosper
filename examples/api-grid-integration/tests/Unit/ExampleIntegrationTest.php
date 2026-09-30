<?php
declare(strict_types=1);
use Zoosper\ApiGrid\ApiGridDataSource;
use Zoosper\ApiGrid\Authentication\NoAuthentication;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Testing\ApiResponseFixture;
use Zoosper\ApiGrid\Testing\FakeApiTransport;
use Zoosper\Examples\ApiGrid\Api\ExampleRequestMapper;
use Zoosper\Examples\ApiGrid\Api\ExampleResponseMapper;
use Zoosper\Grid\DataSource\GridDataSourceCapabilities;
use Zoosper\Grid\DataSource\GridQuery;
it('maps one bounded read-only example without networking',function():void{
    $transport=new FakeApiTransport([ApiResponseFixture::success(['records'=>[['id'=>7,'name'=>'Example']],'total'=>1])]);
    $source=new ApiGridDataSource($transport,new ExampleRequestMapper(),new ExampleResponseMapper(),new NoAuthentication(),new ApiGridContext(1, scope: ['scope_id'=>55]),new GridDataSourceCapabilities());
    expect($source->fetch(new GridQuery())->items)->toBe([['id'=>7,'name'=>'Example']])->and($transport->lastRequest()->query['scope_id'])->toBe(55);
});
