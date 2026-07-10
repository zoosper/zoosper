<?php

declare(strict_types=1);

/**
 * Send a small test email using the configured SMTP transport.
 *
 * Usage:
 *   php tools/send-test-email.php --to=admin@example.test
 *
 * The command prints only non-sensitive outcome metadata. It must never print
 * SMTP passwords, reset tokens, OTPs, TOTP secrets, recovery-code plaintext or
 * provisioning URIs. It uses the shared CLI bootstrap so `.env` is loaded.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$options = getopt('', ['to:', 'subject::']);
$to = (string) ($options['to'] ?? '');
$subject = (string) ($options['subject'] ?? 'Zoosper SMTP test');

if ($to === '') {
    fwrite(STDERR, "Missing required --to=email@example.test option.\n");
    exit(1);
}

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$smtpConfig = new \Zoosper\Mail\Config\SmtpConfig($config);
$probe = new \Zoosper\Mail\Diagnostics\SmtpConnectionProbe($smtpConfig);

if (!$probe->canConnect()) {
    fwrite(STDERR, "SMTP endpoint is not reachable at {$smtpConfig->host()}:{$smtpConfig->port()}. Start a local mail catcher or update SMTP_HOST/SMTP_PORT.\n");
    exit(2);
}

$mailer = new \Zoosper\Mail\Transport\SmtpMailer($smtpConfig);
$message = new \Zoosper\Mail\Message\EmailMessage(
    from: new \Zoosper\Mail\Message\EmailAddress($smtpConfig->fromAddress(), $smtpConfig->fromName()),
    to: [new \Zoosper\Mail\Message\EmailAddress($to)],
    subject: $subject,
    textBody: "This is a Zoosper SMTP configuration test email.\n\nIf you received this, SMTP delivery is working.",
);

try {
    $mailer->send($message);
    print "Test email sent to {$to}.\n";
} catch (\Throwable $exception) {
    fwrite(STDERR, "SMTP test failed: " . $exception->getMessage() . "\n");
    exit(3);
}
