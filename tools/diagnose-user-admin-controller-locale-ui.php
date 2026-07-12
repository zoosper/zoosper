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
    $localeBlock = extract_locale_block($source);
    print "- {$relativePath}:\n";
    print "  locale_field: " . ((str_contains($source, 'name="locale"') || str_contains($source, "name='locale'")) ? 'yes' : 'no') . PHP_EOL;
    print "  locale_block: " . ($localeBlock !== null ? 'yes' : 'no') . PHP_EOL;
    print "  locale_block_php_open_tag: " . ($localeBlock !== null && (str_contains($localeBlock, '<?=') || str_contains($localeBlock, '<?php')) ? 'yes' : 'no') . PHP_EOL;
    print "  email_field: " . ((str_contains($source, 'name="email"') || str_contains($source, "name='email'")) ? 'yes' : 'no') . PHP_EOL;
}

function extract_locale_block(string $source): ?string
{
    if (preg_match('/<div class="admin-form-field admin-form-field--locale">.*?<\/div>/s', $source, $matches) === 1) {
        return $matches[0];
    }

    if (preg_match('/<select[^>]+name=["\']locale["\'][\s\S]*?<\/select>/s', $source, $matches) === 1) {
        return $matches[0];
    }

    return null;
}
