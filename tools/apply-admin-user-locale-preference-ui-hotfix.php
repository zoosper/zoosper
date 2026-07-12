<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$loginController = $basePath . '/app/zoosper-admin/src/Controller/LoginController.php';

print "Zoosper admin user locale UI login-controller hotfix\n";
print "====================================================\n\n";

if (!is_file($loginController)) {
    fwrite(STDERR, "Missing LoginController: {$loginController}\n");
    exit(2);
}

$source = (string) file_get_contents($loginController);
$original = $source;

$patterns = [
    // Remove the multiline block inserted by Phase 1.06 if it landed in LoginController.
    '/\n\s*<div class="admin-form-field admin-form-field--locale">.*?<\/div>\s*/s',
    // Remove any fallback raw locale select block if only the select was inserted.
    '/\n\s*<select[^>]+name=["\']locale["\'][\s\S]*?<\/select>\s*/s',
];

foreach ($patterns as $pattern) {
    $source = preg_replace($pattern, "\n", $source) ?? $source;
}

if ($source === $original) {
    print "- no injected locale UI block found in LoginController\n";
} else {
    $backup = $loginController . '.phase-1.06.1.bak';
    if (!is_file($backup)) {
        copy($loginController, $backup);
        print "- backup created: app/zoosper-admin/src/Controller/LoginController.php.phase-1.06.1.bak\n";
    }
    file_put_contents($loginController, $source);
    print "- removed misplaced locale UI block from LoginController\n";
}

print "Result: OK\n";
