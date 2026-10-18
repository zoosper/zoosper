<?php

declare(strict_types=1);

it('documents central API principal ownership and exact session CSRF semantics', function (): void {
    $root = dirname(__DIR__, 5);
    $api = (string) file_get_contents($root . '/docs/api.md');
    $architecture = (string) file_get_contents($root . '/docs/architecture.md');
    $developer = (string) file_get_contents($root . '/docs/developer-guide.md');
    $decision = (string) file_get_contents($root . '/docs/architecture-decisions/route-registration-ownership.md');

    expect($api)
        ->toContain('BearerPrincipalContext')
        ->toContain('HTTP `419`')
        ->toContain('clears it afterwards')
        ->not->toContain('rejected with a 403 Forbidden status (`csrf_invalid`)');
    expect($architecture)
        ->toContain('Every production bearer controller consumes `BearerPrincipalContext`')
        ->toContain('atomic strictly-newer counter claim');
    expect($developer)
        ->toContain('`ApiBearerAuthenticationMiddleware` authenticates once')
        ->not->toContain('controllers must retain `PersonalAccessTokenAuthenticator`');
    expect($decision)
        ->toContain('API authentication, scope and current-owner permission enforcement now belongs')
        ->not->toContain('does not redefine route access metadata, move API authorisation into middleware');
});
