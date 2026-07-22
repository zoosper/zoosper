<?php

declare(strict_types=1);

use Zoosper\Media\Processing\LocalCopyMediaProcessor;
use Zoosper\Media\Processing\MediaProcessingResult;
use Zoosper\Media\Processing\MediaUploadDerivativeDispatcher;
use Zoosper\Media\Processing\MediaUploadDerivativePolicy;

$basePath = dirname(__DIR__, 3);
require_once $basePath . '/vendor/autoload.php';

print "Zoosper local copy derivative generation smoke\n";
print "==============================================\n\n";

$runtimeRoot = $basePath . '/var/smoke/media-derivatives';
recursiveRemove($runtimeRoot);

$originalRelative = 'storage/media/original/smoke/source.png';
$originalAbsolute = $runtimeRoot . '/' . $originalRelative;
@mkdir(dirname($originalAbsolute), 0775, true);
file_put_contents($originalAbsolute, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAFgwJ/lrWnWQAAAABJRU5ErkJggg=='));

$processor = new LocalCopyMediaProcessor($runtimeRoot);
$dispatcher = new MediaUploadDerivativeDispatcher(
    processor: $processor,
    policy: new MediaUploadDerivativePolicy(true),
);

$result = $dispatcher->processAfterUpload($originalRelative);

$ok = resultSuccessful($result);
$errors = resultErrors($result);
$derivatives = resultDerivatives($result);
$files = [];
$derivativeRoot = $runtimeRoot . '/storage/media/derivatives';
if (is_dir($derivativeRoot)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($derivativeRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $files[] = $file->getPathname();
        }
    }
}

print '- derivative dispatcher enabled: yes' . PHP_EOL;
print '- original file exists: ' . (is_file($originalAbsolute) ? 'ok' : 'FAIL') . PHP_EOL;
print '- processing result success: ' . ($ok ? 'ok' : 'FAIL') . PHP_EOL;
print '- derivative files created: ' . (count($files) > 0 ? 'ok (' . count($files) . ')' : 'FAIL') . PHP_EOL;

if ($derivatives !== []) {
    print '- derivative result entries:' . PHP_EOL;
    foreach ($derivatives as $name => $path) {
        print '  - ' . $name . ': ' . $path . PHP_EOL;
    }
}

if ($errors !== []) {
    print '- processing errors:' . PHP_EOL;
    foreach ($errors as $error) {
        print '  - ' . $error . PHP_EOL;
    }
}

foreach ($files as $file) {
    print '  - ' . str_replace($runtimeRoot . '/', '', $file) . PHP_EOL;
}

print "\nResult: " . ($ok && count($files) > 0 ? 'OK' : 'FAIL') . PHP_EOL;
exit($ok && count($files) > 0 ? 0 : 2);

function resultSuccessful(MediaProcessingResult $result): bool
{
    foreach (['successful', 'success', 'ok'] as $property) {
        if (property_exists($result, $property)) {
            return (bool) $result->{$property};
        }
    }

    foreach (['successful', 'success', 'ok', 'isSuccessful'] as $method) {
        if (method_exists($result, $method)) {
            return (bool) $result->{$method}();
        }
    }

    return false;
}

/** @return list<string> */
function resultErrors(MediaProcessingResult $result): array
{
    foreach (['errors', 'messages'] as $property) {
        if (property_exists($result, $property) && is_array($result->{$property})) {
            return array_values(array_map('strval', $result->{$property}));
        }
    }

    foreach (['errors', 'messages'] as $method) {
        if (method_exists($result, $method)) {
            $value = $result->{$method}();
            if (is_array($value)) {
                return array_values(array_map('strval', $value));
            }
        }
    }

    return [];
}

/** @return array<string, string> */
function resultDerivatives(MediaProcessingResult $result): array
{
    foreach (['derivatives', 'files', 'publicPaths'] as $property) {
        if (property_exists($result, $property) && is_array($result->{$property})) {
            return array_map('strval', $result->{$property});
        }
    }

    foreach (['derivatives', 'files', 'publicPaths'] as $method) {
        if (method_exists($result, $method)) {
            $value = $result->{$method}();
            if (is_array($value)) {
                return array_map('strval', $value);
            }
        }
    }

    return [];
}

function recursiveRemove(string $path): void
{
    if (!is_dir($path)) {
        return;
    }

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );

    foreach ($iterator as $file) {
        $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }

    @rmdir($path);
}
