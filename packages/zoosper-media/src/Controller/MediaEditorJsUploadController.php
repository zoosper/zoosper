<?php

declare(strict_types=1);

namespace Zoosper\Media\Controller;

use RuntimeException;
use Throwable;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Media\EditorJs\EditorJsImageUploadResponseFactory;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaStorage;
use Zoosper\Media\Service\MediaUploadValidator;

/**
 * Handles async image uploads from the Editor.js Image Tool.
 *
 * Route-level authentication, permissions and CSRF validation are handled by the
 * admin middleware pipeline. This controller focuses only on media validation,
 * safe storage, metadata persistence and the Editor.js response contract.
 */
final readonly class MediaEditorJsUploadController
{
    public function __construct(
        private SessionGuard $guard,
        private MediaAssetRepository $assets,
        private MediaUploadValidator $validator,
        private MediaStorage $storage,
        private EditorJsImageUploadResponseFactory $responses,
        private ?ErrorHandler $errorHandler = null,
    ) {
    }

    public function upload(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $file = is_array($_FILES['image'] ?? null) ? $_FILES['image'] : [];
        $validation = $this->validator->validate($file);

        if (!$validation->valid) {
            return Response::json($this->responses->failure(implode(' ', $validation->errors)), 422);
        }

        try {
            $stored = $this->storage->store($file, (string) $validation->extension);
            $assetId = $this->assets->create(
                uuid: $stored->uuid,
                filename: $stored->filename,
                originalFilename: $this->normaliseOriginalFilename((string) ($file['name'] ?? 'upload')),
                mimeType: (string) $validation->mimeType,
                extension: (string) $validation->extension,
                sizeBytes: (int) $validation->sizeBytes,
                storagePath: $stored->storagePath,
                publicPath: $stored->publicPath,
                createdBy: $user->id,
            );
        } catch (Throwable $exception) {
            $this->errorHandler?->logException($exception, [
                'controller' => 'MediaEditorJsUploadController',
                'action' => 'upload',
            ]);

            return Response::json($this->responses->failure('Unable to store uploaded media file.'), 500);
        }

        return Response::json($this->responses->success($stored->publicPath, [
            'id' => $assetId,
            'uuid' => $stored->uuid,
            'name' => $stored->filename,
            'mimeType' => (string) $validation->mimeType,
            'size' => (int) $validation->sizeBytes,
        ]));
    }

    private function normaliseOriginalFilename(string $filename): string
    {
        $filename = trim(str_replace(['\\', '/'], '-', $filename));
        $filename = preg_replace('/[^A-Za-z0-9._-]+/', '-', $filename) ?: 'upload';

        return mb_substr($filename, 0, 255);
    }

    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}
