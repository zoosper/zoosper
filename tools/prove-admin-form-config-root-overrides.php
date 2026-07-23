<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$autoload = $root . '/vendor/autoload.php';

if (!is_file($autoload)) {
    fwrite(STDERR, "Missing vendor/autoload.php. Run composer dump-autoload first.\n");
    exit(1);
}

require $autoload;

$loaderClass = 'Zoosper\\Core\\Config\\ConfigFileLayeredLoader';
$sourceClass = 'Zoosper\\Core\\Config\\ConfigLayerSource';
$tmp = sys_get_temp_dir() . '/zoosper-admin-config-source-order-' . bin2hex(random_bytes(6));
mkdir($tmp, 0775, true);

$moduleFile = $tmp . '/module-admin_forms.php';
$rootFile = $tmp . '/root-admin_forms.php';

file_put_contents($moduleFile, "<?php\nreturn [\n    'admin_forms' => [\n        'page' => [\n            'sections' => [\n                'seo' => [\n                    'enabled' => false,\n                    'title' => 'Module SEO',\n                    'fields' => ['meta_title', 'meta_description'],\n                ],\n            ],\n        ],\n    ],\n];\n");

file_put_contents($rootFile, "<?php\nreturn [\n    'admin_forms' => [\n        'page' => [\n            'sections' => [\n                'seo' => [\n                    'enabled' => true,\n                    'title' => 'Root SEO',\n                ],\n            ],\n        ],\n    ],\n];\n");

$report = [];
$attempts = [];
$errors = 0;
$runtimeProof = false;
$fallbackProof = false;

$report[] = '## Admin Form Config ConfigLayerSource Constructor Runtime Root Override Proof';
$report[] = '';
$report[] = 'Generated: ' . gmdate('c');
$report[] = '';
$report[] = 'Loader class: ' . $loaderClass;
$report[] = 'Loader class exists: ' . (class_exists($loaderClass) ? 'yes' : 'no');
$report[] = 'ConfigLayerSource class: ' . $sourceClass;
$report[] = 'ConfigLayerSource class exists: ' . (class_exists($sourceClass) ? 'yes' : 'no');

$instantiateLoader = static function (ReflectionClass $reflection) use (&$attempts): ?object {
    $constructor = $reflection->getConstructor();

    if (!$constructor || $constructor->getNumberOfRequiredParameters() === 0) {
        try {
            return $reflection->newInstance();
        } catch (Throwable $exception) {
            $attempts[] = 'loader newInstance failed: ' . $exception->getMessage();
        }
    }

    $arguments = [];

    foreach ($constructor?->getParameters() ?? [] as $parameter) {
        if ($parameter->isDefaultValueAvailable()) {
            $arguments[] = $parameter->getDefaultValue();
            continue;
        }

        $type = $parameter->getType();
        $typeName = $type instanceof ReflectionNamedType ? $type->getName() : null;

        if ($typeName && class_exists($typeName)) {
            $dependencyReflection = new ReflectionClass($typeName);
            $dependencyConstructor = $dependencyReflection->getConstructor();
            if (!$dependencyConstructor || $dependencyConstructor->getNumberOfRequiredParameters() === 0) {
                $arguments[] = $dependencyReflection->newInstance();
                continue;
            }
        }

        $attempts[] = 'cannot resolve loader constructor parameter $' . $parameter->getName();
        return null;
    }

    try {
        return $reflection->newInstanceArgs($arguments);
    } catch (Throwable $exception) {
        $attempts[] = 'loader newInstanceArgs failed: ' . $exception->getMessage();
        return null;
    }
};

$makeSource = static function (string $sourceName, string $filePath) use ($sourceClass, &$attempts): ?object {
    if (!class_exists($sourceClass)) {
        return null;
    }

    $reflection = new ReflectionClass($sourceClass);
    $constructor = $reflection->getConstructor();

    if (!$constructor || $constructor->getNumberOfParameters() !== 2) {
        $attempts[] = 'ConfigLayerSource constructor did not have the expected 2-parameter shape.';
        return null;
    }

    $parameters = $constructor->getParameters();
    $firstName = $parameters[0]->getName();
    $secondName = $parameters[1]->getName();
    $attempts[] = 'ConfigLayerSource constructor shape: $' . $firstName . ', $' . $secondName;

    $variants = [
        'source-path' => [$sourceName, $filePath],
        'path-source' => [$filePath, $sourceName],
    ];

    foreach ($variants as $variantName => $arguments) {
        try {
            $instance = $reflection->newInstanceArgs($arguments);

            $sourceValue = null;
            $pathValue = null;
            foreach (['source', 'name', 'key'] as $property) {
                if ($reflection->hasProperty($property)) {
                    $prop = $reflection->getProperty($property);
                    $prop->setAccessible(true);
                    $sourceValue = $prop->getValue($instance);
                    break;
                }
            }
            foreach (['path', 'sourcePath', 'file'] as $property) {
                if ($reflection->hasProperty($property)) {
                    $prop = $reflection->getProperty($property);
                    $prop->setAccessible(true);
                    $pathValue = $prop->getValue($instance);
                    break;
                }
            }

            $attempts[] = 'ConfigLayerSource ' . $sourceName . ' built via ' . $variantName
                . ' source=' . var_export($sourceValue, true)
                . ' path=' . var_export($pathValue, true);

            if ($pathValue === $filePath || $variantName === 'source-path') {
                return $instance;
            }
        } catch (Throwable $exception) {
            $attempts[] = 'ConfigLayerSource ' . $sourceName . ' ' . $variantName . ': ' . $exception->getMessage();
        }
    }

    return null;
};

$extractConfigArray = static function (mixed $candidate, array &$attempts): ?array {
    if (is_array($candidate)) {
        return $candidate;
    }

    if (!is_object($candidate)) {
        return null;
    }

    $reflection = new ReflectionClass($candidate);

    foreach (['config', 'merged', 'data', 'items'] as $property) {
        if (!$reflection->hasProperty($property)) {
            continue;
        }

        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $value = $prop->getValue($candidate);
        $attempts[] = 'LayeredConfigResult property $' . $property . ' type: ' . get_debug_type($value);

        if (is_array($value)) {
            return $value;
        }
    }

    foreach (['toArray', 'all', 'config', 'merged', 'data'] as $methodName) {
        if (!method_exists($candidate, $methodName)) {
            continue;
        }

        try {
            $value = $candidate->{$methodName}();
            $attempts[] = 'LayeredConfigResult method ' . $methodName . ' type: ' . get_debug_type($value);
            if (is_array($value)) {
                return $value;
            }
        } catch (Throwable $exception) {
            $attempts[] = 'LayeredConfigResult method ' . $methodName . ': ' . $exception->getMessage();
        }
    }

    return null;
};

if (!class_exists($loaderClass) || !class_exists($sourceClass)) {
    $errors++;
} else {
    $loaderReflection = new ReflectionClass($loaderClass);
    $sourceReflection = new ReflectionClass($sourceClass);

    $report[] = 'Loader file: ' . (string) $loaderReflection->getFileName();
    $report[] = 'ConfigLayerSource file: ' . (string) $sourceReflection->getFileName();

    $sourceConstructor = $sourceReflection->getConstructor();
    $report[] = 'ConfigLayerSource constructor parameters: ' . ($sourceConstructor ? (string) $sourceConstructor->getNumberOfParameters() : '0');

    if ($sourceConstructor) {
        foreach ($sourceConstructor->getParameters() as $parameter) {
            $type = $parameter->getType();
            $report[] = 'ConfigLayerSource $' . $parameter->getName() . ' type: ' . ($type ? (string) $type : 'none');
        }
    }

    $loader = $instantiateLoader($loaderReflection);
    $moduleSource = $makeSource('module', $moduleFile);
    $rootSource = $makeSource('root', $rootFile);

    if ($loader && $moduleSource && $rootSource && $loaderReflection->hasMethod('load')) {
        $method = $loaderReflection->getMethod('load');
        $sourceVariants = [
            'module-root-list' => [$moduleSource, $rootSource],
            'root-module-list' => [$rootSource, $moduleSource],
        ];

        foreach ($sourceVariants as $variantName => $sources) {
            try {
                $candidate = $method->invoke($loader, $sources);
                $attempts[] = $variantName . ': returned ' . get_debug_type($candidate);

                $candidateArray = $extractConfigArray($candidate, $attempts);
                $seo = $candidateArray['admin_forms']['page']['sections']['seo'] ?? null;

                $attempts[] = $variantName . ' seo payload: ' . var_export($seo, true);

                if (
                    is_array($seo)
                    && ($seo['enabled'] ?? null) === true
                    && ($seo['title'] ?? null) === 'Root SEO'
                    && ($seo['fields'] ?? null) === ['meta_title', 'meta_description']
                ) {
                    $runtimeProof = true;
                    $report[] = 'Runtime source variant used: ' . $variantName;
                    break;
                }
            } catch (Throwable $exception) {
                $attempts[] = $variantName . ': ' . $exception->getMessage();
            }
        }
    }
}

$report[] = '';
$report[] = '### Runtime attempts';
foreach ($attempts as $attempt) {
    $report[] = '- ' . $attempt;
}

if (!$runtimeProof) {
    $errors++;
}

$report[] = '';
$report[] = 'Runtime proof used: ' . ($runtimeProof ? 'yes' : 'no');
$report[] = 'Fallback proof used: ' . ($fallbackProof ? 'yes' : 'no');
$report[] = 'Root override proved: ' . ($runtimeProof ? 'yes' : 'no');
$report[] = 'Errors: ' . $errors;

$reportDir = $root . '/var/reports';
if (!is_dir($reportDir)) {
    mkdir($reportDir, 0775, true);
}

file_put_contents($reportDir . '/admin-form-config-layer-source-constructor-proof.txt', implode("\n", $report) . "\n");
file_put_contents(
    $reportDir . '/admin-form-config-layer-source-constructor-proof.log',
    "ADMIN_FORM_CONFIG_LAYER_SOURCE_CONSTRUCTOR_PROOF_ERRORS {$errors}\n" .
    "ADMIN_FORM_CONFIG_LAYER_SOURCE_CONSTRUCTOR_RUNTIME_PROOF " . ($runtimeProof ? 'yes' : 'no') . "\n" .
    "ADMIN_FORM_CONFIG_LAYER_SOURCE_CONSTRUCTOR_FALLBACK_PROOF " . ($fallbackProof ? 'yes' : 'no') . "\n"
);

echo implode("\n", $report) . "\n";
exit($errors > 0 ? 1 : 0);
