<?php

declare(strict_types=1);

it('keeps reset delivery out of LoggedMailer and the SMTP email log', function (): void {
    $root = dirname(__DIR__, 5);
    $delivery = (string) file_get_contents($root . '/app/zoosper-mail/src/PasswordReset/SmtpAdminPasswordResetDelivery.php');
    $services = (string) file_get_contents($root . '/app/zoosper-mail/config/services.php');
    $authContract = (string) file_get_contents($root . '/app/zoosper-auth/src/PasswordReset/AdminPasswordResetDeliveryInterface.php');
    expect($delivery)->toContain('private SmtpMailer $smtp')
        ->not->toContain('use Zoosper\\Mail\\Transport\\LoggedMailer;')
        ->not->toContain('private LoggedMailer')
        ->not->toContain('use Zoosper\\Mail\\Log\\EmailLogRepository;')
        ->not->toContain('private EmailLogRepository')
        ->and($services)->toContain('AdminPasswordResetDeliveryInterface::class')
        ->toContain('$services->get(SmtpMailer::class)')
        ->and($authContract)->not->toContain('Zoosper\\Mail\\');
});
