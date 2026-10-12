<?php

declare(strict_types=1);

use Zoosper\Core\Security\SecretStrengthPolicy;

it('accepts generated raw hex and base64 secrets', function (): void {
    expect(SecretStrengthPolicy::isStrong(bin2hex(random_bytes(32))))->toBeTrue()
        ->and(SecretStrengthPolicy::isStrong('base64:' . base64_encode(random_bytes(32))))->toBeTrue();
});

it('rejects missing short malformed and placeholder secrets', function (string $value): void {
    expect(SecretStrengthPolicy::isStrong($value))->toBeFalse();
})->with([
    '', 'short-non-placeholder', 'change-me', 'placeholder-private-value',
    'base64:not-valid-***', 'base64:' . base64_encode(random_bytes(16)),
]);
