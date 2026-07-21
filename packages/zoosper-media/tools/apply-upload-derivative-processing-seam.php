<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 3);
$serviceFile = $basePath . '/packages/zoosper-media/src/Service/MediaUploadService.php';
$write = in_array('--write', $argv, true);

print "Zoosper upload derivative processing seam migration\n";
print "===================================================\n\n";
print 'Mode: ' . ($write ? 'apply' : 'dry-run') . PHP_EOL;

if (!is_file($serviceFile)) {
    fwrite(STDERR, "ERROR: MediaUploadService.php not found.\n");
    exit(1);
}

$source = (string) file_get_contents($serviceFile);
$already = str_contains($source, 'MediaUploadDerivativeDispatcher')
    && str_contains($source, 'processAfterUpload($stored->storagePath');

$signals = [
    'MediaUploadService found' => true,
    'already mentions MediaUploadDerivativeDispatcher' => str_contains($source, 'MediaUploadDerivativeDispatcher'),
    'already calls processAfterUpload' => str_contains($source, 'processAfterUpload($stored->storagePath'),
    'has repository persistence call' => str_contains($source, '$this->assets->create'),
    'has success result construction' => str_contains($source, 'MediaUploadServiceResult::success'),
    'has constructor' => preg_match('/public function __construct\s*\(/', $source) === 1,
];

foreach ($signals as $name => $ok) {
    print '- ' . $name . ': ' . ($ok ? 'yes' : 'no') . PHP_EOL;
}

if ($already) {
    print "\nDerivative seam is already applied. Result: OK\n";
    exit(0);
}

if (!$signals['has repository persistence call'] || !$signals['has success result construction'] || !$signals['has constructor']) {
    fwrite(STDERR, "ERROR: Could not safely locate upload success path or constructor. Inspect MediaUploadService manually.\n");
    exit(2);
}

print "\nPlanned changes:\n";
print "- Add optional MediaUploadDerivativeDispatcher dependency to MediaUploadService.\n";
print "- Invoke processAfterUpload() after repository persistence succeeds.\n";
print "- Keep derivative processing disabled by default through MediaUploadDerivativePolicy.\n";
print "- Do not fail the upload when derivative processing returns a failure result.\n";

if (!$write) {
    print "\nDry run only. Re-run with --write to apply.\n";
    exit(0);
}

$backup = $serviceFile . '.phase137n3.bak';
copy($serviceFile, $backup);

$patched = $source;

if (!str_contains($patched, 'use Zoosper\\Media\\Processing\\MediaUploadDerivativeDispatcher;')) {
    $patched = str_replace(
        "namespace Zoosper\\Media\\Service;\n\n",
        "namespace Zoosper\\Media\\Service;\n\nuse Zoosper\\Media\\Processing\\MediaUploadDerivativeDispatcher;\n",
        $patched,
    );
}

if (!str_contains($patched, 'private ?MediaUploadDerivativeDispatcher $derivatives')) {
    $patched = addConstructorDependency($patched, 'private ?MediaUploadDerivativeDispatcher $derivatives = null');
    if ($patched === null) {
        fwrite(STDERR, "ERROR: Could not safely add MediaUploadDerivativeDispatcher constructor dependency.\n");
        copy($backup, $serviceFile);
        exit(2);
    }
}

if (!str_contains($patched, 'processAfterUpload($stored->storagePath')) {
    $patched = preg_replace_callback(
        '/(\$assetId = \$this->assets->create\([\s\S]*?\);)/',
        static fn (array $matches): string => $matches[1] . "\n\n            \$this->derivatives?->processAfterUpload(\$stored->storagePath);",
        $patched,
        1,
        $callReplacements,
    ) ?? $patched;

    if (($callReplacements ?? 0) < 1) {
        fwrite(STDERR, "ERROR: Could not safely add processAfterUpload() call after repository persistence.\n");
        copy($backup, $serviceFile);
        exit(2);
    }
}

file_put_contents($serviceFile, $patched);

print "\nApplied derivative processing seam. Backup: " . $backup . PHP_EOL;
print "Run: php8.5 vendor/bin/pest packages/zoosper-media/tests/Unit/Processing packages/zoosper-media/tests/Unit/Service\n";

function addConstructorDependency(string $source, string $dependency): ?string
{
    $constructorPos = strpos($source, 'public function __construct(');
    if ($constructorPos === false) {
        return null;
    }

    $openParen = strpos($source, '(', $constructorPos);
    if ($openParen === false) {
        return null;
    }

    $length = strlen($source);
    $depth = 0;
    $closeParen = null;
    for ($i = $openParen; $i < $length; $i++) {
        $char = $source[$i];
        if ($char === '(') {
            $depth++;
        } elseif ($char === ')') {
            $depth--;
            if ($depth === 0) {
                $closeParen = $i;
                break;
            }
        }
    }

    if ($closeParen === null) {
        return null;
    }

    $parameterBlock = substr($source, $openParen + 1, $closeParen - $openParen - 1);
    $trimmed = rtrim($parameterBlock);
    $separator = $trimmed === '' ? "\n        " : (str_ends_with($trimmed, ',') ? "\n        " : ",\n        ");
    $replacement = $parameterBlock . $separator . $dependency . "\n    ";

    return substr($source, 0, $openParen + 1) . $replacement . substr($source, $closeParen);
}
