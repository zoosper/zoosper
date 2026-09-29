<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Tests\Unit;

use RuntimeException;
use Zoosper\ApiGrid\ApiGridDataSource;
use Zoosper\ApiGrid\Authentication\NoAuthentication;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Mapping\ApiGridRequestMapperInterface;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMapperInterface;
use Zoosper\ApiGrid\Transport\ApiReliabilityPolicy;
use Zoosper\ApiGrid\Transport\ApiRequest;
use Zoosper\ApiGrid\Transport\ApiResponse;
use Zoosper\ApiGrid\Transport\ApiTransportInterface;
use Zoosper\Grid\DataSource\GridDataSourceCapabilities;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\Grid\DataSource\GridResult;

it('adapts a fake external transport into the neutral Grid result', function (): void {
    $transport = new class implements ApiTransportInterface {
        public ?ApiRequest $request = null;

        public function send(ApiRequest $request, ApiReliabilityPolicy $policy): ApiResponse
        {
            $this->request = $request;
            return new ApiResponse(200, ['records' => [['id' => 7]], 'total' => 1], 35);
        }
    };
    $requestMapper = new class implements ApiGridRequestMapperInterface {
        public function map(GridQuery $query, ApiGridContext $context): ApiRequest
        {
            return new ApiRequest('GET', '/records', [
                'page' => $query->page,
                'per_page' => $query->pageSize,
                'scope_id' => $context->requireInt('scope_id'),
            ]);
        }
    };
    $responseMapper = new class implements ApiGridResponseMapperInterface {
        public function map(ApiResponse $response, GridQuery $query): GridResult
        {
            return new GridResult(
                items: $response->decodedBody['records'],
                total: $response->decodedBody['total'],
                page: $query->page,
                pageSize: $query->pageSize,
            );
        }
    };

    $source = new ApiGridDataSource(
        $transport,
        $requestMapper,
        $responseMapper,
        new NoAuthentication(),
        new ApiGridContext(9, scope: ['scope_id' => 55]),
        new GridDataSourceCapabilities(),
    );
    $result = $source->fetch(new GridQuery(page: 2, pageSize: 5));

    expect($result->items)->toBe([['id' => 7]])
        ->and($result->total)->toBe(1)
        ->and($transport->request?->query)->toBe(['page' => 2, 'per_page' => 5, 'scope_id' => 55]);
});

it('does not convert a non-success response into an empty Grid', function (): void {
    $transport = new class implements ApiTransportInterface {
        public function send(ApiRequest $request, ApiReliabilityPolicy $policy): ApiResponse
        {
            return new ApiResponse(503, [], 0);
        }
    };
    $requestMapper = new class implements ApiGridRequestMapperInterface {
        public function map(GridQuery $query, ApiGridContext $context): ApiRequest
        {
            return new ApiRequest('GET', '/records');
        }
    };
    $responseMapper = new class implements ApiGridResponseMapperInterface {
        public function map(ApiResponse $response, GridQuery $query): GridResult
        {
            return new GridResult([], 0, 1, 20);
        }
    };

    $source = new ApiGridDataSource(
        $transport,
        $requestMapper,
        $responseMapper,
        new NoAuthentication(),
        new ApiGridContext(1),
        new GridDataSourceCapabilities(),
    );

    expect(fn (): GridResult => $source->fetch(new GridQuery()))
        ->toThrow(RuntimeException::class, 'non-success');
});













it('rejects an oversized response from a replaceable transport before mapping', function (): void {
    $transport = new class implements ApiTransportInterface {
        public function send(ApiRequest $request, ApiReliabilityPolicy $policy): ApiResponse
        {
            return new ApiResponse(200, ['records' => [], 'total' => 0], 11);
        }
    };
    $requestMapper = new class implements ApiGridRequestMapperInterface {
        public function map(GridQuery $query, ApiGridContext $context): ApiRequest
        {
            return new ApiRequest('GET', '/records');
        }
    };
    $mapperCalled = false;
    $responseMapper = new class($mapperCalled) implements ApiGridResponseMapperInterface {
        public function __construct(private bool &$called) {}
        public function map(ApiResponse $response, GridQuery $query): GridResult
        {
            $this->called = true;
            return new GridResult([], 0, 1, 20);
        }
    };
    $source = new ApiGridDataSource(
        $transport,
        $requestMapper,
        $responseMapper,
        new NoAuthentication(),
        new ApiGridContext(1),
        new GridDataSourceCapabilities(),
        new ApiReliabilityPolicy(maximumResponseBytes: 10),
    );
    try {
        $source->fetch(new GridQuery());
        test()->fail('Expected the replaceable transport response to be rejected.');
    } catch (\Zoosper\ApiGrid\Transport\ApiTransportException $exception) {
        expect($exception->category)->toBe(\Zoosper\ApiGrid\Transport\ApiTransportException::RESPONSE_TOO_LARGE)
            ->and($mapperCalled)->toBeFalse();
    }
});
