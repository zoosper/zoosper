<?php

declare(strict_types=1);

use Zoosper\Mail\Transport\SmtpMailer;

it('clears the SMTP socket before closing it', function (): void {
    $reflection = new ReflectionClass(SmtpMailer::class);
    $mailer = $reflection->newInstanceWithoutConstructor();
    $socket = fopen('php://temp', 'r+');
    expect(is_resource($socket))->toBeTrue();

    $reflection->getProperty('socket')->setValue($mailer, $socket);
    $reflection->getMethod('disconnect')->invoke($mailer);

    expect($reflection->getProperty('socket')->getValue($mailer))->toBeNull()
        ->and(is_resource($socket))->toBeFalse();
});

it('fails closed for an exhausted SMTP response stream', function (): void {
    $reflection = new ReflectionClass(SmtpMailer::class);
    $mailer = $reflection->newInstanceWithoutConstructor();
    $socket = fopen('php://temp', 'r+');
    expect(is_resource($socket))->toBeTrue();
    $reflection->getProperty('socket')->setValue($mailer, $socket);

    expect(fn () => $reflection->getMethod('expect')->invoke($mailer, [250]))
        ->toThrow(RuntimeException::class, 'Empty SMTP response.');

    $reflection->getMethod('disconnect')->invoke($mailer);
});