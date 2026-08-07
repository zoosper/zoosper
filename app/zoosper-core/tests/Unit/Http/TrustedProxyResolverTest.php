<?php

declare(strict_types=1);

use Zoosper\Core\Http\TrustedProxyResolver;

it('uses the direct peer and ignores spoofed forwarding from an untrusted address', function (): void {
    $resolver = new TrustedProxyResolver(['10.0.0.10']);
    $server = [
        'REMOTE_ADDR' => '203.0.113.20',
        'HTTP_X_FORWARDED_FOR' => '198.51.100.25',
        'HTTP_X_FORWARDED_PROTO' => 'https',
    ];

    expect($resolver->clientIp($server))->toBe('203.0.113.20')
        ->and($resolver->isHttps($server))->toBeFalse();
});

it('uses the first valid untrusted client from a trusted proxy chain', function (): void {
    $resolver = new TrustedProxyResolver(['10.0.0.10', '10.0.0.11']);
    $server = [
        'REMOTE_ADDR' => '10.0.0.10',
        'HTTP_X_FORWARDED_FOR' => '198.51.100.25, 10.0.0.11',
        'HTTP_X_FORWARDED_PROTO' => 'https',
    ];

    expect($resolver->clientIp($server))->toBe('198.51.100.25')
        ->and($resolver->isHttps($server))->toBeTrue();
});

it('supports ipv6 peers and rejects malformed addresses', function (): void {
    $resolver = new TrustedProxyResolver(['2001:db8::10']);

    expect($resolver->clientIp(['REMOTE_ADDR' => '2001:db8::10', 'HTTP_X_FORWARDED_FOR' => '2001:db8::20']))
        ->toBe('2001:db8::20')
        ->and($resolver->clientIp(['REMOTE_ADDR' => 'not-an-ip']))->toBeNull();
});
