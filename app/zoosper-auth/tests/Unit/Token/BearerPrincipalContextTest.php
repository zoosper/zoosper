<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Unit\Token;

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Token\BearerPrincipalContext;
use Zoosper\Auth\Token\PersonalAccessToken;
use Zoosper\Auth\Token\PersonalAccessTokenPrincipal;
use Zoosper\Core\Http\Request;
use Zoosper\Errors\ZoosperException;

function contextPrincipal(): PersonalAccessTokenPrincipal
{
    return new PersonalAccessTokenPrincipal(
        new PersonalAccessToken(1, 'pub', 1, 'test', hash('sha256', 'fixture-token'), ['pages:read'], null, null, null, '2026-01-01'),
        new AdminUser(1, 'user@test', 'User', 'hash', 'active', ['page.view']),
    );
}

it('keys a principal to the exact Request object and clears it explicitly', function (): void {
    $context = new BearerPrincipalContext();
    $request = new Request('GET', '/api/v1/pages');
    $other = new Request('GET', '/api/v1/pages');
    $principal = contextPrincipal();
    $context->establish($request, $principal);
    expect($context->has($request))->toBeTrue()
        ->and($context->require($request))->toBe($principal)
        ->and($context->has($other))->toBeFalse();
    $context->clear($request);
    expect($context->has($request))->toBeFalse();
});

it('fails closed when middleware has not established a principal', function (): void {
    expect(fn () => (new BearerPrincipalContext())->require(new Request('GET', '/api/v1/pages')))
        ->toThrow(ZoosperException::class, 'Bearer principal is unavailable for this request.');
});
