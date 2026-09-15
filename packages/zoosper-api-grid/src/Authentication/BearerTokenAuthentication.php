<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Authentication;

use InvalidArgumentException;
use Zoosper\ApiGrid\Transport\ApiRequest;

/** Adds one redaction-safe bearer credential to an immutable API Grid request. */
final readonly class BearerTokenAuthentication implements ApiAuthenticationInterface
{
    public function __construct(private string $token)
    {
        if ($token === '' || preg_match('/[\x00-\x20\x7f]/', $token) === 1) {
            throw new InvalidArgumentException('API Grid bearer token must be a non-empty header-safe value.');
        }
    }

    public function apply(ApiRequest $request): ApiRequest
    {
        return $request->withHeaders(['Authorization' => 'Bearer ' . $this->token]);
    }
}
