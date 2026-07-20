<?php

declare(strict_types=1);

$basePath = dirname(__DIR__, 3);
$controller = $basePath . '/packages/zoosper-media/src/Controller/MediaAdminController.php';
$service = $basePath . '/packages/zoosper-media/src/Service/MediaUploadService.php';
$write = in_array('--write', $argv, true);

print "Zoosper media admin upload service migration\n";
print "===========================================\n\n";

if (!is_file($controller)) {
    fail('MediaAdminController.php not found: ' . $controller);
}
if (!is_file($service)) {
    fail('MediaUploadService.php not found: ' . $service);
}

$source = (string) file_get_contents($controller);
$original = $source;

$signals = [
    'mentions MediaUploadService' => str_contains($source, 'MediaUploadService'),
    'direct storage store' => str_contains($source, '->storage->store'),
    'direct assets create' => str_contains($source, '->assets->create'),
    'has currentAdminUser helper' => str_contains($source, 'currentAdminUser'),
    'has normaliseOriginalFilename helper' => str_contains($source, 'normaliseOriginalFilename'),
];

foreach ($signals as $label => $value) {
    print '- ' . $label . ': ' . ($value ? 'yes' : 'no') . PHP_EOL;
}
print PHP_EOL;

if (!$signals['direct storage store'] && !$signals['direct assets create']) {
    print "MediaAdminController already appears migrated. No source changes required.\n";
    exit(0);
}

if (!preg_match('/public function upload\s*\((.*?)\)\s*:\s*([^\s{]+)\s*\{(.*?)(?=\n    public function|\n    private function|\n}\s*$)/s', $source, $match)) {
    fail('Could not locate public upload() method safely. Run inspect-media-admin-upload-migration.php and migrate manually.');
}

$uploadParams = trim($match[1]);
$returnType = trim($match[2]);
$uploadBody = $match[3];

if (!str_contains($uploadBody, '$_FILES') || !str_contains($uploadBody, 'storage') || !str_contains($uploadBody, 'assets')) {
    fail('upload() method does not match the expected direct media persistence shape. No changes made.');
}

$source = addUse($source, 'Zoosper\\Media\\Service\\MediaUploadService');
$source = addUse($source, 'Zoosper\\Media\\Service\\MediaUploadServiceResult');

if (!str_contains($source, 'private MediaUploadService $uploads;')) {
    $source = preg_replace('/final readonly class MediaAdminController\s*\{\s*/', "final readonly class MediaAdminController\n{\n    private MediaUploadService \$uploads;\n\n", $source, 1) ?? $source;
}

if (preg_match('/public function __construct\s*\((.*?)\)\s*\{/s', $source, $ctorMatch)) {
    $ctorParams = $ctorMatch[1];
    if (!str_contains($ctorParams, 'MediaUploadService')) {
        $newCtorParams = rtrim($ctorParams);
        $newCtorParams = preg_replace('/,?\s*$/', '', $newCtorParams) ?? $newCtorParams;
        $newCtorParams .= ",\n        ?MediaUploadService \$uploads = null,\n    ";
        $source = substr_replace($source, $newCtorParams, strpos($source, $ctorParams), strlen($ctorParams));
    }

    if (!str_contains($source, '$this->uploads = $uploads ?? new MediaUploadService(')) {
        $ctorOpen = strpos($source, '{', strpos($source, 'public function __construct'));
        if ($ctorOpen === false) {
            fail('Could not locate constructor body start.');
        }
        $assignment = "\n        \$this->uploads = \$uploads ?? new MediaUploadService(\n            assets: \$assets,\n            validator: \$validator,\n            storage: \$storage,\n            basePath: dirname(__DIR__, 5),\n            errorHandler: \$errorHandler ?? null,\n        );\n";
        $source = substr_replace($source, $assignment, $ctorOpen + 1, 0);
    }
} else {
    fail('Could not locate constructor safely.');
}

$newUpload = buildUploadMethod($uploadParams, $returnType, $uploadBody);
$source = str_replace($match[0], $newUpload, $source);

$source = removePrivateMethod($source, 'normaliseOriginalFilename');

if ($source === $original) {
    print "No changes produced.\n";
    exit(0);
}

print "Planned changes:\n";
print "- Add MediaUploadService dependency\n";
print "- Delegate upload() to shared service\n";
print "- Preserve current upload() return type: {$returnType}\n";
print "- Remove duplicated normaliseOriginalFilename() helper if present\n\n";

if (!$write) {
    print "Dry run only. Re-run with --write to apply.\n";
    exit(0);
}

$backup = $controller . '.phase137r3.bak';
file_put_contents($backup, $original);
file_put_contents($controller, $source);
print "Applied migration. Backup written to: " . basename($backup) . PHP_EOL;

function addUse(string $source, string $use): string
{
    if (str_contains($source, 'use ' . $use . ';')) {
        return $source;
    }

    $needle = "namespace Zoosper\\Media\\Controller;\n";
    if (!str_contains($source, $needle)) {
        return $source;
    }

    return str_replace($needle, $needle . "\nuse " . $use . ";", $source);
}

function buildUploadMethod(string $params, string $returnType, string $oldBody): string
{
    $fileField = str_contains($oldBody, "\$_FILES['media']") ? 'media' : 'file';
    if (str_contains($oldBody, "\$_FILES['image']")) {
        $fileField = 'image';
    }

    $successReturn = detectSuccessRedirect($oldBody);
    $failureReturn = detectFailureRedirect($oldBody);

    return "public function upload({$params}): {$returnType}\n    {\n        \$file = is_array(\$_FILES['{$fileField}'] ?? null) ? \$_FILES['{$fileField}'] : [];\n        \$result = \$this->uploads->upload(\$file, \$this->currentAdminUser());\n\n        if (!\$result->successful) {\n            {$failureReturn}\n        }\n\n        {$successReturn}\n    }";
}

function detectSuccessRedirect(string $body): string
{
    if (preg_match('/return\s+Response::redirect\(([^;]*media[^;]*)\);/i', $body, $match)) {
        return 'return Response::redirect(' . trim($match[1]) . ');';
    }

    return "return Response::redirect('/admin/media');";
}

function detectFailureRedirect(string $body): string
{
    if (preg_match('/return\s+Response::redirect\(([^;]*media[^;]*)\);/i', $body, $match)) {
        return 'return Response::redirect(' . trim($match[1]) . ');';
    }

    return "return Response::redirect('/admin/media');";
}

function removePrivateMethod(string $source, string $method): string
{
    return preg_replace('/\n    private function ' . preg_quote($method, '/') . '\s*\([^)]*\)\s*:\s*[^\s{]+\s*\{.*?\n    \}/s', '', $source, 1) ?? $source;
}

function fail(string $message): never
{
    fwrite(STDERR, $message . PHP_EOL);
    exit(2);
}
