<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Authentication;

use Zoosper\ApiGrid\Transport\ApiRequest;

final class NoAuthentication implements ApiAuthenticationInterface
{
    #[\Override]
    public function apply(ApiRequest $request): ApiRequest
    {
        return $request;
    }
}











