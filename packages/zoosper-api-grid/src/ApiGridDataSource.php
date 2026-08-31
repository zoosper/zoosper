<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid;

use Zoosper\ApiGrid\Authentication\ApiAuthenticationInterface;
use Zoosper\ApiGrid\Mapping\ApiGridContext;
use Zoosper\ApiGrid\Mapping\ApiGridRequestMapperInterface;
use Zoosper\ApiGrid\Mapping\ApiGridResponseMapperInterface;
use Zoosper\ApiGrid\Transport\ApiReliabilityPolicy;
use Zoosper\ApiGrid\Transport\ApiTransportException;
use Zoosper\ApiGrid\Transport\ApiTransportInterface;
use Zoosper\Grid\DataSource\GridDataSourceCapabilities;
use Zoosper\Grid\DataSource\GridDataSourceInterface;
use Zoosper\Grid\DataSource\GridQuery;
use Zoosper\Grid\DataSource\GridResult;

final readonly class ApiGridDataSource implements GridDataSourceInterface
{
    public function __construct(
        private ApiTransportInterface $transport,
        private ApiGridRequestMapperInterface $requestMapper,
        private ApiGridResponseMapperInterface $responseMapper,
        private ApiAuthenticationInterface $authentication,
        private ApiGridContext $context,
        private GridDataSourceCapabilities $capabilities,
        private ApiReliabilityPolicy $policy = new ApiReliabilityPolicy(),
    ) {
    }

    public function capabilities(): GridDataSourceCapabilities
    {
        return $this->capabilities;
    }

    public function fetch(GridQuery $query): GridResult
    {
        $request = $this->authentication->apply($this->requestMapper->map($query, $this->context));
        $response = $this->transport->send($request, $this->policy);

        // Keep this defensive boundary for custom/fake transports that return
        // ApiResponse directly instead of using CurlJsonApiTransport.
        if (!$response->isSuccessful()) {
            throw new ApiTransportException(
                'External Grid source returned a non-success response.',
                category: ApiTransportException::NON_SUCCESS,
                statusCode: $response->statusCode,
            );
        }

        return $this->responseMapper->map($response, $query);
    }
}











