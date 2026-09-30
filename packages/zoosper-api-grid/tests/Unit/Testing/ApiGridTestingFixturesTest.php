<?php
declare(strict_types=1);
namespace Zoosper\ApiGrid\Tests\Unit\Testing;
use Zoosper\ApiGrid\Testing\ApiResponseFixture;
use Zoosper\ApiGrid\Testing\FakeApiTransport;
use Zoosper\ApiGrid\Transport\ApiReliabilityPolicy;
use Zoosper\ApiGrid\Transport\ApiRequest;
it('queues deterministic responses and records requests without networking', function (): void {
    $transport=new FakeApiTransport([ApiResponseFixture::success(['records'=>[]])]);
    $response=$transport->send(new ApiRequest('GET','/records',['page'=>2]),new ApiReliabilityPolicy());
    expect($response->statusCode)->toBe(200)->and($transport->lastRequest()->query)->toBe(['page'=>2]);
});
it('fails loudly when a fake transport is exhausted', function (): void {
    expect(fn()=>(new FakeApiTransport([]))->send(new ApiRequest('GET','/records'),new ApiReliabilityPolicy()))->toThrow(\OutOfBoundsException::class);
});
it('builds payload-free failure fixtures only for non-success statuses', function (): void {
    expect(ApiResponseFixture::failure(503)->decodedBody)->toBe([])
        ->and(fn()=>ApiResponseFixture::failure(200))->toThrow(\InvalidArgumentException::class);
});
