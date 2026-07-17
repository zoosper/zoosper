<?php

declare(strict_types=1);

namespace Zoosper\Media\Controller;

use RuntimeException;
use Throwable;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Admin\UI\AdminViewRenderer;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaStorage;
use Zoosper\Media\Service\MediaUploadValidator;

/**
 * Admin controller for the media library foundation.
 *
 * Authentication, permission and POST CSRF decisions belong to the admin
 * middleware pipeline. This controller still generates form tokens and handles
 * media-specific validation/storage orchestration.
 */
final readonly class MediaAdminController
{
    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminLayout $layout,
        private AdminViewRenderer $views,
        private MediaAssetRepository $assets,
        private MediaUploadValidator $validator,
        private MediaStorage $storage,
        private ?ErrorHandler $errorHandler = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();

        return Response::html($this->views->render(
            'Media',
            'zoosper-media::admin/media/index',
            [
                'assets' => $this->assets->latest(),
                'uploadUrl' => '/admin/media/upload',
            ],
            $user,
            'media',
        ));
    }

    public function uploadForm(Request $request): Response
    {
        $user = $this->currentAdminUser();

        return Response::html($this->views->render(
            'Upload media',
            'zoosper-media::admin/media/upload',
            [
                'action' => '/admin/media/upload',
                'csrfToken' => $this->csrf->token(),
                'errors' => [],
            ],
            $user,
            'media',
        ));
    }

    public function upload(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $file = is_array($_FILES['media_file'] ?? null) ? $_FILES['media_file'] : [];
        $validation = $this->validator->validate($file);

        if (!$validation->valid) {
            return $this->uploadErrorResponse($user, $validation->errors, 422);
        }

        try {
            $stored = $this->storage->store($file, (string) $validation->extension);
            $this->assets->create(
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
            $this->errorHandler?->logException($exception, ['controller' => 'MediaAdminController', 'action' => 'upload']);

            return $this->uploadErrorResponse($user, ['Unable to store uploaded media file.'], 500);
        }

        return Response::redirect('/admin/media');
    }

    /** @param list<string> $errors */
    private function uploadErrorResponse(AdminUser $user, array $errors, int $status): Response
    {
        return Response::html($this->views->render(
            'Upload media',
            'zoosper-media::admin/media/upload',
            [
                'action' => '/admin/media/upload',
                'csrfToken' => $this->csrf->token(),
                'errors' => $errors,
            ],
            $user,
            'media',
        ), $status);
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
