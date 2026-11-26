<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Tests\Unit;

use Zoosper\ApiGrid\Authentication\NoAuthentication;
use Zoosper\ApiGrid\Transport\ApiRequest;

it('leaves requests and their headers unchanged', function (): void {
    $request = new ApiRequest('GET', '/records', ['page' => 2], ['Accept' => 'application/json']);

    expect((new NoAuthentication())->apply($request))->toBe($request);
});