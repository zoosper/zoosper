<?php

declare(strict_types=1);

use Zoosper\Core\Http\TrustedProxyResolver;

it('uses the direct peer and ignores spoofed forwarding from an untrusted address', function (): void {
    $resolver = new TrustedProxyResolver(['10.0.0.10']);
    $server = ['REMOTE_ADDR' => '203.0.113.20', 'HTTP_X_FORWARDED_FOR' => '198.51.100.25', 'HTTP_X_FORWARDED_PROTO' => 'https'];
    expect($resolver->clientIp($server))->toBe('203.0.113.20')->and($resolver->isHttps($server))->toBeFalse();
});

it('walks a trusted proxy chain right to left and ignores attacker prepended values', function (): void {
    $resolver = new TrustedProxyResolver(['10.0.0.0/24']);
    $server = [
        'REMOTE_ADDR' => '10.0.0.10',
        'HTTP_X_FORWARDED_FOR' => '192.0.2.66, 198.51.100.25, 10.0.0.11',
        'HTTP_X_FORWARDED_PROTO' => 'https',
    ];
    expect($resolver->clientIp($server))->toBe('198.51.100.25')->and($resolver->isHttps($server))->toBeTrue();
});

it('supports exact and cidr trusted networks for ipv4 and ipv6', function (): void {
    $resolver = new TrustedProxyResolver(['10.0.0.0/24', '2001:db8:1::/48']);
    expect($resolver->clientIp(['REMOTE_ADDR' => '10.0.0.200', 'HTTP_X_FORWARDED_FOR' => '198.51.100.9']))->toBe('198.51.100.9')
        ->and($resolver->clientIp(['REMOTE_ADDR' => '2001:db8:1::10', 'HTTP_X_FORWARDED_FOR' => '2001:db8:2::20']))->toBe('2001:db8:2::20')
        ->and($resolver->clientIp(['REMOTE_ADDR' => '2001:db8:3::10', 'HTTP_X_FORWARDED_FOR' => '192.0.2.9']))->toBe('2001:db8:3::10');
});

it('fails closed to the trusted peer when any forwarded token is malformed', function (): void {
    $resolver = new TrustedProxyResolver(['10.0.0.0/24']);
    expect($resolver->clientIp(['REMOTE_ADDR' => '10.0.0.10', 'HTTP_X_FORWARDED_FOR' => '198.51.100.25, invalid, 10.0.0.11']))->toBe('10.0.0.10')
        ->and($resolver->clientIp(['REMOTE_ADDR' => 'not-an-ip']))->toBeNull();
});

it('rejects invalid trusted proxy entries instead of silently weakening trust', function (string $entry): void {
    expect(fn (): TrustedProxyResolver => new TrustedProxyResolver([$entry]))->toThrow(InvalidArgumentException::class);
})->with(['hostname', '10.0.0.0/33', '2001:db8::/129', '10.0.0.1/not-a-prefix']);

it('uses the process value when dotenv exposes only an empty environment placeholder', function (): void {
    $originalEnvironment = $_ENV['TRUSTED_PROXIES'] ?? null;
    $hadEnvironment = array_key_exists('TRUSTED_PROXIES', $_ENV);
    $originalProcess = getenv('TRUSTED_PROXIES');
    try {
        $_ENV['TRUSTED_PROXIES'] = '';
        putenv('TRUSTED_PROXIES=10.0.0.0/24');
        $resolver = TrustedProxyResolver::fromEnvironment();
        expect($resolver->isHttps(['REMOTE_ADDR' => '10.0.0.10', 'HTTP_X_FORWARDED_PROTO' => 'https']))->toBeTrue();
    } finally {
        if ($hadEnvironment) {
            $_ENV['TRUSTED_PROXIES'] = $originalEnvironment;
        } else {
            unset($_ENV['TRUSTED_PROXIES']);
        }
        if ($originalProcess === false) {
            putenv('TRUSTED_PROXIES');
        } else {
            putenv('TRUSTED_PROXIES=' . $originalProcess);
        }
    }
});
