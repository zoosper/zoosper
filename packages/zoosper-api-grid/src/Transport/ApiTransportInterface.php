<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Transport;

interface ApiTransportInterface
{
    /** @throws ApiTransportException */
    public function send(ApiRequest $request, ApiReliabilityPolicy $policy): ApiResponse;
}











