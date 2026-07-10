<?php

declare(strict_types=1);

/**
 * Print redacted mail configuration diagnostics.
 *
 * This tool is read-only. It must never print SMTP passwords, message bodies,
 * reset tokens, OTPs, TOTP secrets, recovery-code plaintext or provisioning
 * URIs. It uses the shared CLI bootstrap so `.env` is loaded consistently.
 */

$basePath = require __DIR__ . '/bootstrap.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$smtp = new \Zoosper\Mail\Config\SmtpConfig($config);
$inspector = new \Zoosper\Mail\Diagnostics\MailConfigurationInspector($config, $smtp);
$probe = new \Zoosper\Mail\Diagnostics\SmtpConnectionProbe($smtp);
$delivery = new \Zoosper\Mail\Diagnostics\SmtpDeliveryModeInspector($smtp);

print "Zoosper mail configuration diagnostics\n";
print "=====================================\n\n";

foreach ($inspector->summary()->toArray() as $key => $value) {
    print str_pad($key, 22) . ': ' . (is_bool($value) ? ($value ? 'yes' : 'no') : (string) $value) . PHP_EOL;
}

$reachable = $probe->canConnect();
print str_pad('smtp_reachable', 22) . ': ' . ($reachable ? 'yes' : 'no') . PHP_EOL;
print str_pad('delivery_mode', 22) . ': ' . $delivery->mode() . PHP_EOL;
print str_pad('delivery_note', 22) . ': ' . $delivery->explanation() . PHP_EOL;

$warnings = $inspector->warnings();
if (!$reachable) {
    $warnings[] = 'SMTP endpoint is not reachable. Start a local mail catcher on the configured host/port or update SMTP_HOST/SMTP_PORT.';
}

if ($delivery->mode() === 'local_mail_catcher') {
    $warnings[] = 'Mail is being accepted by a local catcher and will not arrive in the real recipient inbox.';
}

print "\nWarnings:\n";
if ($warnings === []) {
    print "- none\n";
} else {
    foreach ($warnings as $warning) {
        print '- ' . $warning . PHP_EOL;
    }
}
