<?php

declare(strict_types=1);

/**
 * Print redacted mail configuration diagnostics.
 *
 * This tool is read-only. It must never print SMTP passwords, message bodies,
 * reset tokens, OTPs, TOTP secrets, recovery-code plaintext or provisioning
 * URIs.
 */

$basePath = dirname(__DIR__);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $_ENV) && $_ENV[$key] !== '') {
            return $_ENV[$key];
        }

        $value = getenv($key);
        return $value !== false && $value !== '' ? $value : $default;
    }
}

require $basePath . '/vendor/autoload.php';

$config = \Zoosper\Core\Config\ConfigRepository::fromPath($basePath . '/config');
$smtp = new \Zoosper\Mail\Config\SmtpConfig($config);
$inspector = new \Zoosper\Mail\Diagnostics\MailConfigurationInspector($config, $smtp);
$probe = new \Zoosper\Mail\Diagnostics\SmtpConnectionProbe($smtp);

print "Zoosper mail configuration diagnostics\n";
print "=====================================\n\n";

foreach ($inspector->summary()->toArray() as $key => $value) {
    print str_pad($key, 22) . ': ' . (is_bool($value) ? ($value ? 'yes' : 'no') : (string) $value) . PHP_EOL;
}

print str_pad('smtp_reachable', 22) . ': ' . ($probe->canConnect() ? 'yes' : 'no') . PHP_EOL;

$warnings = $inspector->warnings();
if (!$probe->canConnect()) {
    $warnings[] = 'SMTP endpoint is not reachable. Start a local mail catcher on the configured host/port or update SMTP_HOST/SMTP_PORT.';
}

print "\nWarnings:\n";
if ($warnings === []) {
    print "- none\n";
} else {
    foreach ($warnings as $warning) {
        print '- ' . $warning . PHP_EOL;
    }
}
