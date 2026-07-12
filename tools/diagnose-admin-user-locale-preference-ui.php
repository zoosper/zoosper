<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';

print "Zoosper admin user locale preference UI diagnostics\n";
print "===================================================\n\n";

foreach ([
    'app/zoosper-admin/src/Controller/LoginController.php',
    'app/zoosper-admin/src/Controller/UserAdminController.php',
] as $relativePath) {
    $path = $basePath . '/' . $relativePath;
    if (!is_file($path)) {
        print "- {$relativePath}: missing\n";
        continue;
    }

    $source = (string) file_get_contents($path);
    print '- ' . $relativePath . ': locale field=' . ((str_contains($source, 'name="locale"') || str_contains($source, "name='locale'")) ? 'yes' : 'no') . PHP_EOL;
}
