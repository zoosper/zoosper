<?php

declare(strict_types=1);

namespace Zoosper\Auth\Token;

use WeakMap;
use Zoosper\Core\Http\Request;
use Zoosper\Errors\ZoosperException;

/**
 * Request-object keyed bearer identity established by API authentication middleware.
 *
 * @psalm-api Runtime boundary shared by bearer middleware and API controllers.
 */
final class BearerPrincipalContext
{
    /** @var WeakMap<Request, PersonalAccessTokenPrincipal> */
    private WeakMap $principals;

    public function __construct()
    {
        /** @var WeakMap<Request, PersonalAccessTokenPrincipal> $principals */
        $principals = new WeakMap();
        $this->principals = $principals;
    }

    public function establish(Request $request, PersonalAccessTokenPrincipal $principal): void
    {
        $this->principals[$request] = $principal;
    }

    public function require(Request $request): PersonalAccessTokenPrincipal
    {
        if (!isset($this->principals[$request])) {
            throw new ZoosperException(
                message: 'Bearer principal is unavailable for this request.',
                context: 'A bearer API controller was invoked without successful central authentication and authorisation.',
                suggestion: 'Dispatch the request through the registered API middleware pipeline before invoking the controller.',
                details: ['method' => $request->method(), 'path' => $request->path()],
            );
        }

        $principal = $this->principals[$request];
        if (!$principal instanceof PersonalAccessTokenPrincipal) {
            throw new ZoosperException(
                message: 'Bearer principal has an invalid runtime type.',
                context: 'The request-keyed bearer principal context contains an unexpected value.',
                suggestion: 'Ensure only PersonalAccessTokenPrincipal values are established by API authentication middleware.',
                details: ['method' => $request->method(), 'path' => $request->path()],
            );
        }

        return $principal;
    }

    public function has(Request $request): bool
    {
        return isset($this->principals[$request]);
    }

    public function clear(Request $request): void
    {
        unset($this->principals[$request]);
    }
}
