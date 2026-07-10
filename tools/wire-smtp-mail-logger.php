<?php

declare(strict_types=1);

/**
 * Idempotently wire SMTP mail logging into ApplicationFactory.
 *
 * This script updates the latest local ApplicationFactory instead of replacing
 * it from a stale snapshot. It adds EmailLogRepository and LoggedMailer wiring
 * around SmtpMailer so every MailerInterface send is recorded.
 */

$file = dirname(__DIR__) . '/app/zoosper-core/src/Bootstrap/ApplicationFactory.php';
$code = file_get_contents($file);
if ($code === false) {
    fwrite(STDERR, "Unable to read ApplicationFactory.php\n");
    exit(1);
}

$replacements = [
    'use Zoosper\\Mail\\Config\\SmtpConfig;\n' => "use Zoosper\\Mail\\Config\\SmtpConfig;\nuse Zoosper\\Mail\\Log\\EmailLogRepository;\n",
    'use Zoosper\\Mail\\Transport\\MailerInterface;\n' => "use Zoosper\\Mail\\Transport\\LoggedMailer;\nuse Zoosper\\Mail\\Transport\\MailerInterface;\n",
    '$themeRepository = new ThemeRepository($basePath . \'/themes\');' => "$themeRepository = new ThemeRepository($basePath . '/themes');\n        $emailLogRepository = new EmailLogRepository($pdo);",
    '$mailer = new SmtpMailer($smtpConfig);' => "$smtpMailer = new SmtpMailer($smtpConfig);\n        $mailer = new LoggedMailer($smtpMailer, $emailLogRepository);",
    '$services->set(ThemeRepository::class, $themeRepository);' => "$services->set(ThemeRepository::class, $themeRepository);\n        $services->set(EmailLogRepository::class, $emailLogRepository);",
    '$services->set(SmtpMailer::class, $mailer);' => '$services->set(SmtpMailer::class, $smtpMailer);',
];

foreach ($replacements as $search => $replace) {
    if (str_contains($code, $replace)) {
        continue;
    }
    $code = str_replace($search, $replace, $code);
}

file_put_contents($file, $code);
print "ApplicationFactory SMTP mail logger wiring applied.\n";
