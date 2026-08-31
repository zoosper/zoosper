<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Authentication;

use Zoosper\ApiGrid\Transport\ApiRequest;

interface ApiAuthenticationInterface
{
    public function apply(ApiRequest $request): ApiRequest;
}











