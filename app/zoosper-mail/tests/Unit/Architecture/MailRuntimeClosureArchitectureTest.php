<?php

declare(strict_types=1);

it('registers one shared scope repository and derives the Default SMTP service from the factory', function (): void {
    $root = dirname(__DIR__, 5);
    $services = file_get_contents($root . '/app/zoosper-mail/config/services.php');

    expect(substr_count($services, 'new ScopeConfigRepository('))->toBe(1)
        ->and($services)->toContain('SmtpConfigFactory::class')
        ->toContain('->get(SmtpConfigFactory::class)')
        ->toContain('->forDefaultScope()')
        ->toContain('MailConfigurationInspectorFactory::class');
});

it('does not change the transport interface to smuggle scope through send', function (): void {
    $root = dirname(__DIR__, 5);
    $interface = file_get_contents($root . '/app/zoosper-mail/src/Transport/MailerInterface.php');

    expect($interface)->toContain('public function send(EmailMessage $message): void;')
        ->not->toContain('ScopeContext');
});
