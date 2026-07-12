<?php

declare(strict_types=1);

$basePath = require __DIR__ . '/bootstrap.php';
$configPath = $basePath . '/config/i18n.php';

print "Zoosper supported admin locales config apply\n";
print "============================================\n\n";

$config = is_file($configPath) ? require $configPath : [];
if (!is_array($config)) {
    fwrite(STDERR, "config/i18n.php must return an array.\n");
    exit(2);
}

if (isset($config['supported_admin_locales']) && is_array($config['supported_admin_locales'])) {
    print "- supported_admin_locales already exists\n";
    print "Result: OK\n";
    exit(0);
}

$config['supported_admin_locales'] = [
    'en_AU' => 'English (Australia)',
];

$backupPath = $configPath . '.phase-1.05.bak';
if (is_file($configPath) && !is_file($backupPath)) {
    copy($configPath, $backupPath);
    print "- backup created: config/i18n.php.phase-1.05.bak\n";
}

file_put_contents($configPath, render_php_array($config));
print "- added supported_admin_locales\n";
print "Result: OK\n";

/** @param array<string, mixed> $config */
function render_php_array(array $config): string
{
    $lines = [];
    $lines[] = '<?php';
    $lines[] = '';
    $lines[] = 'declare(strict_types=1);';
    $lines[] = '';
    $lines[] = 'return [';
    foreach ($config as $key => $value) {
        if (is_array($value)) {
            $lines[] = "    '" . addslashes((string) $key) . "' => [";
            foreach ($value as $nestedKey => $nestedValue) {
                $lines[] = "        '" . addslashes((string) $nestedKey) . "' => '" . addslashes((string) $nestedValue) . "',";
            }
            $lines[] = '    ],';
            continue;
        }

        $lines[] = "    '" . addslashes((string) $key) . "' => '" . addslashes((string) $value) . "',";
    }
    $lines[] = '];';

    return implode(PHP_EOL, $lines) . PHP_EOL;
}
