<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin flash message verification\n";
print "========================================\n\n";

$checks = [
    'FlashMessage' => class_exists(\Zoosper\Admin\Message\FlashMessage::class),
    'FlashMessageStoreInterface' => interface_exists(\Zoosper\Admin\Message\FlashMessageStoreInterface::class),
    'SessionFlashMessageStore' => class_exists(\Zoosper\Admin\Message\SessionFlashMessageStore::class),
    'FlashMessageRenderer' => class_exists(\Zoosper\Admin\Message\FlashMessageRenderer::class),
    'message css' => is_file($basePath . '/public/assets/admin/css/zoosper-admin-messages.css'),
    'message js' => is_file($basePath . '/public/assets/admin/js/zoosper-admin-messages.js'),
];

$renderer = new \Zoosper\Admin\Message\FlashMessageRenderer();
$html = $renderer->render([
    new \Zoosper\Admin\Message\FlashMessage('success', 'Page saved successfully.', 'page.saved'),
]);
$checks['renderer output'] = str_contains($html, 'Page saved successfully.') && str_contains($html, 'data-message-key="page.saved"');

$failed = false;
foreach ($checks as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
    $failed = $failed || !$ok;
}

print "\nResult: " . ($failed ? 'FAIL' : 'OK') . PHP_EOL;
exit($failed ? 2 : 0);
