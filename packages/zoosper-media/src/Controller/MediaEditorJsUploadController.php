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
 *
 * CORRECTNESS FIX (confirmed 2026-07-30, external reviewer pass): upload()
 * previously called `(string) $result->stored?->publicPath` — since $stored
 * is only ever null when $result->successful is false (already checked and
 * returned above this line), reaching this exact line with a null $stored
 * should be structurally impossible per MediaUploadService's own success()
 * contract. But the old code silently tolerated that impossible state
 * anyway: a null $stored would have quietly produced publicPath="" inside
 * an HTTP 200 "successful" JSON response, rather than surfacing as an
 * error — masking what would actually be a real bug in MediaUploadService
 * if it were ever hit.
 *
 * Fixed by explicitly checking the invariant and throwing loudly if it is
 * ever violated, rather than silently degrading. This can never fire under
 * MediaUploadService's current, correct behaviour — it exists purely as a
 * fail-loud guard against a future regression in that contract.
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

        // CORRECTNESS FIX: fail loudly rather than silently masking an
        // impossible-per-contract null $stored inside a "successful" response.
        if ($result->stored === null) {
            $exception = new RuntimeException(
                'MediaUploadServiceResult reported success but $stored is null. '
                . 'This indicates a bug in MediaUploadService::upload() — its success() '
                . 'factory should never be called without a real StoredMediaFile.'
            );
            $this->errorHandler?->logException($exception, ['controller' => 'MediaEditorJsUploadController', 'action' => 'upload']);

            throw $exception;
        }

        return Response::json($this->responses->success($result->stored->publicPath, $result->metadata));
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
