<?php

declare(strict_types=1);

namespace Zoosper\Media\Controller;

use RuntimeException;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Log\ErrorHandler;
use Zoosper\Media\EditorJs\EditorJsImageUploadResponseFactory;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaStorage;
use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Media\Service\MediaUploadValidator;

/**
 * Handles async image uploads from the Editor.js Image Tool.
 *
 * Route-level authentication, permissions and CSRF validation are handled by the
 * admin middleware pipeline. Upload validation, storage, metadata persistence and
 * orphan-file cleanup are centralised in MediaUploadService.
 */
final readonly class MediaEditorJsUploadController
{
    private MediaUploadService $uploads;

    public function __construct(
        private SessionGuard $guard,
        MediaAssetRepository $assets,
        MediaUploadValidator $validator,
        MediaStorage $storage,
        private EditorJsImageUploadResponseFactory $responses,
        private ?ErrorHandler $errorHandler = null,
        ?MediaUploadService $uploads = null,
    ) {
        $this->uploads = $uploads ?? new MediaUploadService(
            assets: $assets,
            validator: $validator,
            storage: $storage,
            basePath: dirname(__DIR__, 5),
            errorHandler: $errorHandler,
        );
    }

    public function upload(Request $request): Response
    {
        $file = is_array($_FILES['image'] ?? null) ? $_FILES['image'] : [];
        $result = $this->uploads->upload($file, $this->currentAdminUser());

        if (!$result->successful) {
            return Response::json($this->responses->failure($result->message), $result->statusCode);
        }

        return Response::json($this->responses->success((string) $result->stored?->publicPath, $result->metadata));
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
