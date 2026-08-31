<?php

declare(strict_types=1);

use Zoosper\Core\Http\Request;

it('owns canonical upload through a request-carried stateless PAT boundary', function (): void {
    $root = dirname(__DIR__, 3);
    $routes = require $root . '/config/api_routes.php';
    $controller = (string) file_get_contents($root . '/src/Api/MediaApiController.php');

    $matches = array_values(array_filter(
        $routes,
        static fn (array $route): bool => ($route['method'] ?? null) === 'POST'
            && ($route['path'] ?? null) === '/api/v1/media',
    ));

    expect($matches)->toHaveCount(1)
        ->and($matches[0]['action'] ?? null)->toBe('upload')
        ->and($matches[0]['public'] ?? null)->toBeTrue()
        ->and($matches[0]['stateless'] ?? null)->toBeTrue()
        ->and($controller)->toContain('private MediaUploadService $uploads')
        ->toContain("can('media.manage')");

    $uploadStart = strpos($controller, 'public function upload(Request $request): Response');
    $archiveStart = strpos($controller, 'public function archive(Request $request): Response');
    expect($uploadStart)->not->toBeFalse()->and($archiveStart)->not->toBeFalse();

    $upload = substr($controller, (int) $uploadStart, (int) $archiveStart - (int) $uploadStart);
    expect($upload)
        ->toContain("'media:upload'")
        ->toContain("\$request->uploadedFile('file')")
        ->toContain('$this->uploads->upload($file, $principal->user)')
        ->toContain("'media_upload_failed'")
        ->toContain("'media_reload_failed'")
        ->toContain("'media.api_uploaded'")
        ->toContain("'token_id' => \$principal->token->id")
        ->toContain("'token_public_id' => \$principal->token->publicId")
        ->toContain("'derivatives' => array_map")
        ->toContain('], 201)')
        ->not->toContain('$_FILES')
        ->not->toContain('storage_path')
        ->not->toContain('tokenHash');
});

it('cannot substitute a conflicting process global for the uploaded file carried by the request', function (): void {
    $originalFiles = $_FILES;

    try {
        $_FILES = ['file' => ['name' => 'global.png', 'error' => UPLOAD_ERR_OK]];
        $request = new Request('POST', '/api/v1/media', files: [
            'file' => ['name' => 'request.png', 'error' => UPLOAD_ERR_OK],
        ]);

        expect($request->uploadedFile('file'))->toBe([
            'name' => 'request.png',
            'error' => UPLOAD_ERR_OK,
        ]);
    } finally {
        $_FILES = $originalFiles;
    }
});











