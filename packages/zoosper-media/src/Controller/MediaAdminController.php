<?php

declare(strict_types=1);

namespace Zoosper\Media\Controller;

use Zoosper\Media\Service\MediaUploadServiceResult;
use Zoosper\Media\Service\MediaUploadService;
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
    private MediaUploadService $uploads;

public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminLayout $layout,
        private AdminViewRenderer $views,
        private MediaAssetRepository $assets,
        private MediaUploadValidator $validator,
        private MediaStorage $storage,
        private ?ErrorHandler $errorHandler = null,
        ?MediaUploadService $uploads = null,
    ) {
        $this->uploads = $uploads ?? new MediaUploadService(
            assets: $assets,
            validator: $validator,
            storage: $storage,
            basePath: dirname(__DIR__, 5),
            errorHandler: $errorHandler ?? null,
        );

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
        $file = is_array($_FILES['file'] ?? null) ? $_FILES['file'] : [];
        $result = $this->uploads->upload($file, $this->currentAdminUser());

        if (!$result->successful) {
            return Response::redirect('/admin/media');
        }

        return Response::redirect('/admin/media');
    }
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


    private function currentAdminUser(): AdminUser
    {
        $user = $this->guard->user();
        if ($user === null) {
            throw new RuntimeException('Authenticated admin user required after middleware guard.');
        }

        return $user;
    }
}
