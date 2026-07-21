<?php

declare(strict_types=1);

require dirname(__DIR__, 3) . '/tools/bootstrap.php';

use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaStorage;
use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Media\Service\MediaUploadValidator;

print "Zoosper media upload integration-test readiness probe\n";
print "====================================================\n\n";

$classes = [
    MediaUploadService::class,
    MediaUploadValidator::class,
    MediaStorage::class,
    MediaAssetRepository::class,
];

$ok = true;
foreach ($classes as $class) {
    try {
        $reflection = new ReflectionClass($class);
    } catch (ReflectionException $exception) {
        print '- ' . $class . ': missing (' . $exception->getMessage() . ')' . PHP_EOL;
        $ok = false;
        continue;
    }

    print '- ' . $class . PHP_EOL;
    print '  final          : ' . ($reflection->isFinal() ? 'yes' : 'no') . PHP_EOL;
    print '  instantiable   : ' . ($reflection->isInstantiable() ? 'yes' : 'no') . PHP_EOL;
    print '  constructor    : ' . ($reflection->getConstructor() !== null ? 'yes' : 'no') . PHP_EOL;

    foreach (['validate', 'store', 'create', 'upload'] as $method) {
        if ($reflection->hasMethod($method)) {
            $methodReflection = $reflection->getMethod($method);
            print '  method ' . $method . ' final: ' . ($methodReflection->isFinal() ? 'yes' : 'no') . PHP_EOL;
        }
    }
}

print "\nRequired integration-test seams:\n" . PHP_EOL;
$seams = [
    'MediaStorage can be substituted or safely fixture-driven' => canSubstitute(MediaStorage::class, 'store'),
    'MediaAssetRepository can be substituted or safely fixture-driven' => canSubstitute(MediaAssetRepository::class, 'create'),
    'MediaUploadValidator can be substituted or safely fixture-driven' => canSubstitute(MediaUploadValidator::class, 'validate'),
];

foreach ($seams as $label => $available) {
    print '- ' . $label . ': ' . ($available ? 'yes' : 'needs concrete fixture') . PHP_EOL;
}

print "\nRecommended next test strategy:\n" . PHP_EOL;
print '- Use concrete fixture setup when a class is final or not safely substitutable.' . PHP_EOL;
print '- Prefer a temporary filesystem root and SQLite-backed repository fixture.' . PHP_EOL;
print '- Avoid brittle reflection mutation of readonly/final dependencies.' . PHP_EOL;

print "\nResult: " . ($ok ? 'OK' : 'FAIL') . PHP_EOL;
exit($ok ? 0 : 2);

function canSubstitute(string $class, string $method): bool
{
    try {
        $reflection = new ReflectionClass($class);
        if ($reflection->isFinal() || !$reflection->hasMethod($method)) {
            return false;
        }

        return !$reflection->getMethod($method)->isFinal();
    } catch (ReflectionException) {
        return false;
    }
}
