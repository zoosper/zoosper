<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$paths = [
    'app/zoosper-admin/src/Controller/UserAdminController.php',
    'app/zoosper-admin/src/Controller/LoginController.php',
];

print "Zoosper UserAdminController locale UI diagnostics\n";
print "=================================================\n\n";

foreach ($paths as $relativePath) {
    $path = $basePath . '/' . $relativePath;
    if (!is_file($path)) {
        print "- {$relativePath}: missing\n";
        continue;
    }

    $source = (string) file_get_contents($path);
    print "- {$relativePath}:\n";
    print "  locale_field: " . ((str_contains($source, 'name="locale"') || str_contains($source, "name='locale'")) ? 'yes' : 'no') . PHP_EOL;
    print "  email_field: " . ((str_contains($source, 'name="email"') || str_contains($source, "name='email'")) ? 'yes' : 'no') . PHP_EOL;
    print "  form_marker: " . ((str_contains($source, '<form') || str_contains($source, 'form')) ? 'yes' : 'no') . PHP_EOL;
}
