<?php

declare(strict_types=1);

use Zoosper\TwoFactor\Challenge\TwoFactorChallengeResult;

it('exposes a non-null Admin User identity only for a successful challenge', function (): void {
    expect(TwoFactorChallengeResult::success(17)->successfulAdminUserId())->toBe(17);
});

it('fails closed when a failed challenge is asked for a successful identity', function (TwoFactorChallengeResult $result): void {
    expect(fn (): int => $result->successfulAdminUserId())
        ->toThrow(\LogicException::class, 'A failed two-factor challenge has no successful Admin User identity.');
})->with([
    'wrong code' => [TwoFactorChallengeResult::wrongCode(17)],
    'invalid or expired' => [TwoFactorChallengeResult::invalidOrExpired()],
]);
