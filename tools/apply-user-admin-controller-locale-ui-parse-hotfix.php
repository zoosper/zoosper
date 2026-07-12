<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$userAdminController = $basePath . '/app/zoosper-admin/src/Controller/UserAdminController.php';

print "Zoosper UserAdminController locale UI parse hotfix\n";
print "==================================================\n\n";

if (!is_file($userAdminController)) {
    fwrite(STDERR, "Missing UserAdminController: {$userAdminController}\n");
    exit(2);
}

$source = (string) file_get_contents($userAdminController);
$original = $source;

$patterns = [
    '/\n\s*<div class="admin-form-field admin-form-field--locale">.*?<\/div>\s*/s',
    '/\n\s*<select[^>]+name=["\']locale["\'][\s\S]*?<\/select>\s*/s',
];

foreach ($patterns as $pattern) {
    $source = preg_replace($pattern, "\n", $source) ?? $source;
}

if ($source === $original) {
    print "- no raw locale UI block found in UserAdminController\n";
} else {
    $backup = $userAdminController . '.phase-1.07.1.bak';
    if (!is_file($backup)) {
        copy($userAdminController, $backup);
        print "- backup created: app/zoosper-admin/src/Controller/UserAdminController.php.phase-1.07.1.bak\n";
    }

    file_put_contents($userAdminController, $source);
    print "- removed raw PHP/HTML locale UI block from UserAdminController\n";
}

print "Result: OK\n";
