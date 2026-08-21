<?php

declare(strict_types=1);

namespace Zoosper\Media\Controller;

use RuntimeException;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Url\AdminUrlGenerator;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Media\Lifecycle\MediaLifecycleCoordinator;
use Zoosper\Media\Admin\Grid\MediaVisualGridWorkspace;

/**
 * Admin controller for the media library foundation.
 *
 * Authentication, permission and POST CSRF decisions belong to the admin
 * middleware pipeline. This controller still generates form tokens and handles
 * media-specific validation/storage orchestration.
 *
 * Phase 1.41 (page decoupling round 2): $views is typed to
 * Zoosper\Auth\UI\AdminViewRendererInterface instead of the concrete
 * Zoosper\Admin\UI\AdminViewRenderer; the previously-unused $layout
 * (AdminLayout) constructor parameter was removed entirely.
 *
 * BUG FIX (independently flagged by two reviewer passes): upload() silently
 * redirected to /admin/media on ANY failure (wrong file type, too large,
 * corrupt file, storage error), giving the admin zero feedback — even
 * though uploadErrorResponse() below already existed, fully implemented,
 * and correctly re-renders the form with the real error message. It was
 * simply never called. This is now wired up: upload() calls
 * uploadErrorResponse() with $result->message (from
 * MediaUploadServiceResult, which already carries a human-readable message
 * on every failure path — validation errors joined, or "Unable to store
 * uploaded media file." on a storage/DB failure) and $result->statusCode
 * (422 for validation failures, 500 for storage failures), matching the
 * status codes MediaUploadServiceResult already defines.
 */
final readonly class MediaAdminController
{

    public function __construct(
        private SessionGuard $guard,
        private CsrfTokenManager $csrf,
        private AdminViewRendererInterface $views,
        private MediaAssetRepository $assets,
        private MediaUploadService $uploads,
        private ?AdminUrlGenerator $adminUrls = null,
        private ?MediaLifecycleCoordinator $lifecycle = null,
        private ?MediaVisualGridWorkspace $visualGrid = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->currentAdminUser();
        if ($this->visualGrid === null) {
            throw new RuntimeException('Media visual Grid service is required.');
        }
        return Response::html($this->views->render(
            'Media',
            'zoosper-media::admin/media/index',
            [
                'gridHtml' => $this->visualGrid->render(
                    $user->id,
                    $request,
                    $this->adminUrl('media'),
                    $this->csrf->token(),
                ),
                'uploadUrl' => $this->adminUrl('media/upload'),
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
                'action' => $this->adminUrl('media/upload'),
                'csrfToken' => $this->csrf->token(),
                'errors' => [],
                'backUrl' => $this->adminUrl('media'),
            ],
            $user,
            'media',
        ));
    }

    public function upload(Request $request): Response
    {
        $user = $this->currentAdminUser();
        $file = $request->uploadedFile('media_file');
        $result = $this->uploads->upload($file, $user);

        if (!$result->successful) {
            return $this->uploadErrorResponse($user, [$result->message], $result->statusCode);
        }

        return Response::redirect($this->adminUrl('media'));
    }

    public function archive(Request $request): Response
    {
        return $this->lifecycleOperation($request, 'archive');
    }

    public function restore(Request $request): Response
    {
        return $this->lifecycleOperation($request, 'restore');
    }

    public function deletePermanently(Request $request): Response
    {
        return $this->lifecycleOperation($request, 'delete');
    }

    private function lifecycleOperation(Request $request, string $operation): Response
    {
        $user = $this->currentAdminUser();
        $id = (int) ($request->routeParam('id') ?? 0);
        $asset = $id > 0 ? $this->assets->findById($id) : null;
        if ($asset === null || $this->lifecycle === null) {
            return Response::redirect($this->adminUrl('media'), 303);
        }
        match ($operation) {
            'archive' => $this->lifecycle->archive($asset, $user->id, $user->email),
            'restore' => $this->lifecycle->restore($asset, $user->id, $user->email),
            'delete' => $this->lifecycle->deletePermanently($asset, $user->id, $user->email),
            default => throw new \LogicException('Unsupported Media lifecycle operation.'),
        };
        return Response::redirect($this->adminUrl('media'), 303);
    }

    private function uploadErrorResponse(AdminUser $user, array $errors, int $status): Response
    {
        return Response::html($this->views->render(
            'Upload media',
            'zoosper-media::admin/media/upload',
            [
                'action' => $this->adminUrl('media/upload'),
                'csrfToken' => $this->csrf->token(),
                'errors' => $errors,
                'backUrl' => $this->adminUrl('media'),
            ],
            $user,
            'media',
        ), $status);
    }


    private function adminUrl(string $path = ''): string
    {
        if ($this->adminUrls !== null) {
            return $this->adminUrls->url($path);
        }

        return $path === '' ? '/admin' : '/admin/' . ltrim($path, '/');
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
