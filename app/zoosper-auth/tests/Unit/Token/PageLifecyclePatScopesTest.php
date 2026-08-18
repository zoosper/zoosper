<?php
declare(strict_types=1);
use Zoosper\Auth\Token\PersonalAccessTokenService;
it('declares narrow Page lifecycle PAT scopes without wildcard authority', function (): void { expect(PersonalAccessTokenService::SCOPES)->toContain('pages:archive')->toContain('pages:delete')->not->toContain('pages:*'); });
