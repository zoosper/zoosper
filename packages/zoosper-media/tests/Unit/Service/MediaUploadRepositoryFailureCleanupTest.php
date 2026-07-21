<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Unit\Service;

use PDO;
use ReflectionClass;
use ReflectionNamedType;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaStorage;
use Zoosper\Media\Service\MediaStoredFileCleanupService;
use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Media\Service\MediaUploadValidator;

test('media upload service cleans stored files when repository persistence fails after storage', function () {
    $root = sys_get_temp_dir() . '/zoosper-media-upload-db-failure-' . bin2hex(random_bytes(4));
    mkdir($root, 0775, true);

    $tmpFile = $root . '/upload.png';
    file_put_contents($tmpFile, base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/p9sAAAAASUVORK5CYII='
    ));

    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $service = new MediaUploadService(
        assets: new MediaAssetRepository($pdo),
        validator: new MediaUploadValidator(),
        storage: new MediaStorage($root),
        basePath: $root,
        errorHandler: null,
        cleanup: new MediaStoredFileCleanupService($root),
    );

    $result = $service->upload([
        'name' => 'failure-path.png',
        'type' => 'image/png',
        'tmp_name' => $tmpFile,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($tmpFile),
    ], mediaUploadTestAdminUser());

    expect($result->successful)->toBeFalse();
    expect($result->statusCode)->toBe(500);
    expect($result->message)->toBe('Unable to store uploaded media file.');
    expect(mediaUploadFixtureFiles($root . '/storage/media'))->toBe([]);
    expect(mediaUploadFixtureFiles($root . '/public/media'))->toBe([]);
});

/** @return list<string> */
function mediaUploadFixtureFiles(string $directory): array
{
    if (!is_dir($directory)) {
        return [];
    }

    $files = [];
    $iterator = new \RecursiveIteratorIterator(
        new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
    );

    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $files[] = $file->getPathname();
        }
    }

    sort($files);

    return $files;
}

function mediaUploadTestAdminUser(): AdminUser
{
    $reflection = new ReflectionClass(AdminUser::class);
    $constructor = $reflection->getConstructor();

    if ($constructor !== null) {
        $args = [];
        foreach ($constructor->getParameters() as $parameter) {
            $name = strtolower($parameter->getName());
            $type = $parameter->getType();
            $typeName = $type instanceof ReflectionNamedType ? $type->getName() : null;

            $args[] = match (true) {
                $name === 'id' || str_ends_with($name, 'id') => 1,
                str_contains($name, 'email') => 'admin@example.test',
                str_contains($name, 'username') => 'admin',
                str_contains($name, 'name') => 'Admin User',
                str_contains($name, 'password') || str_contains($name, 'hash') => 'hash',
                str_contains($name, 'locale') => null,
                str_contains($name, 'created') || str_contains($name, 'updated') => '2026-01-01 00:00:00',
                $typeName === 'int' => 1,
                $typeName === 'bool' => true,
                $typeName === 'array' => [],
                $typeName === 'string' => 'test',
                $parameter->allowsNull() => null,
                $parameter->isDefaultValueAvailable() => $parameter->getDefaultValue(),
                default => null,
            };
        }

        try {
            return $reflection->newInstanceArgs($args);
        } catch (\Throwable) {
            // Fall through to constructor-bypass fixture below.
        }
    }

    $user = $reflection->newInstanceWithoutConstructor();
    if ($reflection->hasProperty('id')) {
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($user, 1);
    }

    return $user;
}
