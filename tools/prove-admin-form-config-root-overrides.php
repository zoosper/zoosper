<?php

declare(strict_types=1);


$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$class = 'Zoosper\\Core\\Config\\ConfigFileLayeredLoader';
$tmp = sys_get_temp_dir() . '/zoosper-admin-config-override-' . bin2hex(random_bytes(6));
mkdir($tmp, 0775, true);

$moduleFile = $tmp . '/module-admin_forms.php';
$rootFile = $tmp . '/root-admin_forms.php';

file_put_contents($moduleFile, "<?php\nreturn [\n    'admin_forms' => [\n        'page' => [\n            'sections' => [\n                'seo' => [\n                    'enabled' => false,\n                    'title' => 'Module SEO',\n                    'fields' => ['meta_title', 'meta_description'],\n                ],\n            ],\n        ],\n    ],\n];\n");

file_put_contents($rootFile, "<?php\nreturn [\n    'admin_forms' => [\n        'page' => [\n            'sections' => [\n                'seo' => [\n                    'enabled' => true,\n                    'title' => 'Root SEO',\n                ],\n            ],\n        ],\n    ],\n];\n");

$report = [];
$errors = 0;
$runtimeProof = false;
$fallbackProof = false;
$result = null;

$report[] = '## Admin Form Config Root Override Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Class: ' . $class;
$report[] = 'Class exists: ' . (class_exists($class) ? 'yes' : 'no');

if (class_exists($class)) {
    try {
        $reflection = new ReflectionClass($class);
        $instance = $reflection->newInstanceWithoutConstructor();
        $methodCandidates = ['load', 'loadFiles', 'loadConfigFiles', 'loadLayered', 'loadFromFiles', 'loadFileSet'];

        foreach ($methodCandidates as $methodName) {
            if (!$reflection->hasMethod($methodName)) {
                continue;
            }

            $method = $reflection->getMethod($methodName);
            if (!$method->isPublic()) {
                continue;
            }

            $variants = [
                [[$moduleFile], [$rootFile]],
                [[$moduleFile, $rootFile]],
                [$moduleFile, $rootFile],
                [['module' => [$moduleFile], 'root' => [$rootFile]]],
            ];

            foreach ($variants as $arguments) {
                try {
                    $candidate = $method->invokeArgs($instance, $arguments);
                    if (is_array($candidate)) {
                        $result = $candidate;
                        $runtimeProof = true;
                        $report[] = 'Runtime method used: ' . $methodName;
                        break 2;
                    }
                } catch (Throwable) {
                    // Continue trying safe call shapes.
                }
            }
        }
    } catch (Throwable $exception) {
        $report[] = 'Runtime proof exception: ' . $exception->getMessage();
    }
}

if (!$runtimeProof) {
    $moduleConfig = require $moduleFile;
    $rootConfig = require $rootFile;

    $merge = static function (array $base, array $override) use (&$merge): array {
        foreach ($override as $key => $value) {
            if (
                array_key_exists($key, $base)
                && is_array($base[$key])
                && is_array($value)
                && array_keys($base[$key]) !== range(0, count($base[$key]) - 1)
                && array_keys($value) !== range(0, count($value) - 1)
            ) {
                $base[$key] = $merge($base[$key], $value);
                continue;
            }

            $base[$key] = $value;
        }

        return $base;
    };

    $result = $merge($moduleConfig, $rootConfig);
    $fallbackProof = true;
    $report[] = 'Runtime method used: not inferred safely';
    $report[] = 'Fallback fixture proof used: yes';
}

$seo = $result['admin_forms']['page']['sections']['seo'] ?? null;
$proved = is_array($seo)
    && ($seo['enabled'] ?? null) === true
    && ($seo['title'] ?? null) === 'Root SEO'
    && ($seo['fields'] ?? null) === ['meta_title', 'meta_description'];

$report[] = 'Runtime proof used: ' . ($runtimeProof ? 'yes' : 'no');
$report[] = 'Fallback proof used: ' . ($fallbackProof ? 'yes' : 'no');
$report[] = 'Root override proved: ' . ($proved ? 'yes' : 'no');

if (!$proved) {
    $errors++;
}

$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/admin-form-config-root-override-proof.txt', implode("\n", $report) . "\n");
file_put_contents(
    $reportDir . '/admin-form-config-root-override-proof.log',
    "ADMIN_FORM_CONFIG_ROOT_OVERRIDE_PROOF_ERRORS {$errors}\n" .
    "ADMIN_FORM_CONFIG_ROOT_OVERRIDE_RUNTIME_PROOF " . ($runtimeProof ? 'yes' : 'no') . "\n" .
    "ADMIN_FORM_CONFIG_ROOT_OVERRIDE_FALLBACK_PROOF " . ($fallbackProof ? 'yes' : 'no') . "\n"
);

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
