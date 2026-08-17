<?php

declare(strict_types=1);

use Zoosper\Auth\Token\PersonalAccessTokenService;

it('publishes least-privilege scopes for URL Rewrite Site and Theme APIs', function (): void {
    expect(PersonalAccessTokenService::SCOPES)->toContain(
        'url_rewrites:read',
        'url_rewrites:write',
        'sites:read',
        'sites:write',
        'themes:read',
        'themes:write',
    );
});
