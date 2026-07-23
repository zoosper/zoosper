<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$bridgeClass = 'Zoosper\\Admin\\Form\\AdminConfigLayeredFileLoader';
$tmp = sys_get_temp_dir() . '/zoosper-admin-layered-bridge-' . bin2hex(random_bytes(6));
mkdir($tmp, 0775, true);

$moduleFile = $tmp . '/module-admin_forms.php';
$rootFile = $tmp . '/root-admin_forms.php';

file_put_contents($moduleFile, "<?php\nreturn [\n    'admin_forms' => [\n        'page' => [\n            'sections' => [\n                'seo' => [\n                    'enabled' => false,\n                    'title' => 'Module SEO',\n                    'fields' => ['meta_title', 'meta_description'],\n                ],\n            ],\n        ],\n    ],\n];\n");

file_put_contents($rootFile, "<?php\nreturn [\n    'admin_forms' => [\n        'page' => [\n            'sections' => [\n                'seo' => [\n                    'enabled' => true,\n                    'title' => 'Root SEO',\n                ],\n            ],\n        ],\n    ],\n];\n");

$report = [];
$errors = 0;

$report[] = '## Admin Config Layered Runtime Bridge Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Bridge class: ' . $bridgeClass;
$report[] = 'Bridge class exists: ' . (class_exists($bridgeClass) ? 'yes' : 'no');

if (!class_exists($bridgeClass)) {
    $errors++;
} else {
    $loader = new $bridgeClass();
    $config = $loader->load([
        'module:test-admin-forms' => $moduleFile,
        'root:test-admin-forms' => $rootFile,
    ]);

    $seo = $config['admin_forms']['page']['sections']['seo'] ?? null;
    $proved = is_array($seo)
        && ($seo['enabled'] ?? null) === true
        && ($seo['title'] ?? null) === 'Root SEO'
        && ($seo['fields'] ?? null) === ['meta_title', 'meta_description'];

    $report[] = 'Root override proved: ' . ($proved ? 'yes' : 'no');
    $report[] = 'SEO payload: ' . var_export($seo, true);

    if (!$proved) {
        $errors++;
    }
}

$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/admin-config-layered-runtime-bridge.txt', implode("\n", $report) . "\n");
file_put_contents($reportDir . '/admin-config-layered-runtime-bridge.log', "ADMIN_CONFIG_LAYERED_RUNTIME_BRIDGE_ERRORS {$errors}\n");

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
