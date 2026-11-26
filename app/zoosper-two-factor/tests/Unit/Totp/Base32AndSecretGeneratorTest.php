<?php

declare(strict_types=1);

use Zoosper\TwoFactor\Totp\Base32;
use Zoosper\TwoFactor\Totp\TotpSecretGenerator;

it('encodes RFC 4648 vectors without padding and decodes them', function (string $plain, string $encoded): void {
    expect(Base32::encode($plain))->toBe($encoded)
        ->and(Base32::decode($encoded))->toBe($plain);
})->with([
    ['', ''],
    ['f', 'MY'],
    ['fo', 'MZXQ'],
    ['foo', 'MZXW6'],
    ['foob', 'MZXW6YQ'],
    ['fooba', 'MZXW6YTB'],
    ['foobar', 'MZXW6YTBOI'],
    ["\xff\x00\x80", '74AIA'],
]);

it('generates secrets with the requested byte length', function (): void {
    $generator = new TotpSecretGenerator();

    expect(strlen(Base32::decode($generator->generate())))->toBe(20)
        ->and(strlen(Base32::decode($generator->generate(1))))->toBe(1);
});

it('rejects nonpositive secret lengths with the random_bytes error', function (int $length): void {
    expect(fn () => (new TotpSecretGenerator())->generate($length))
        ->toThrow(ValueError::class, 'random_bytes(): Argument #1 ($length) must be greater than 0');
})->with([0, -1]);