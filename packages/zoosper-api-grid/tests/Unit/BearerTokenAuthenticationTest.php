<?php

declare(strict_types=1);

namespace Zoosper\ApiGrid\Tests\Unit;

use InvalidArgumentException;
use Zoosper\ApiGrid\Authentication\BearerTokenAuthentication;
use Zoosper\ApiGrid\Transport\ApiRequest;

it('adds a bearer credential without changing request method endpoint or query', function (): void {
    $request = new ApiRequest('GET', '/orders', ['page' => 2], ['X-Trace' => 'safe']);
    $authenticated = (new BearerTokenAuthentication('secret-token'))->apply($request);
    expect($authenticated->method)->toBe('GET')
        ->and($authenticated->endpoint)->toBe('/orders')
        ->and($authenticated->query)->toBe(['page' => 2])
        ->and($authenticated->headers)->toBe([
            'X-Trace' => 'safe',
            'Authorization' => 'Bearer secret-token',
        ]);
});

it('rejects empty and header-injection token values', function (string $token): void {
    expect(fn () => new BearerTokenAuthentication($token))->toThrow(InvalidArgumentException::class);
})->with(['', "token\r\nX-Injected: yes", 'token with space']);
