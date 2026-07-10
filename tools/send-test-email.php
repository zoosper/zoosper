<?php

declare(strict_types=1);

/**
 * Send a small test email using LoggedMailer so success/failure is logged.
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
$pdo = (new \Zoosper\Core\Database\ConnectionFactory($config, $basePath))->create();
$smtpConfig = new \Zoosper\Mail\Config\SmtpConfig($config);
$probe = new \Zoosper\Mail\Diagnostics\SmtpConnectionProbe($smtpConfig);
$inner = new \Zoosper\Mail\Transport\SmtpMailer($smtpConfig);
$mailer = new \Zoosper\Mail\Transport\LoggedMailer($inner, new \Zoosper\Mail\Log\EmailLogRepository($pdo));

$message = new \Zoosper\Mail\Message\EmailMessage(
    from: new \Zoosper\Mail\Message\EmailAddress($smtpConfig->fromAddress(), $smtpConfig->fromName()),
    to: [new \Zoosper\Mail\Message\EmailAddress($to)],
    subject: $subject,
    textBody: "This is a Zoosper SMTP configuration test email.\n\nIf you received this, SMTP delivery is working.",
);

try {
    if (!$probe->canConnect()) {
        throw new RuntimeException("SMTP endpoint is not reachable at {$smtpConfig->host()}:{$smtpConfig->port()}.");
    }
    $mailer->send($message);
    print "Test email sent and logged to {$to}.\n";
} catch (\Throwable $exception) {
    try {
        (new \Zoosper\Mail\Log\EmailLogRepository($pdo))->recordFailure(bin2hex(random_bytes(16)), $message, $exception);
    } catch (\Throwable) {
    }
    fwrite(STDERR, "SMTP test failed and was logged: " . $exception->getMessage() . "\n");
    exit(3);
}
