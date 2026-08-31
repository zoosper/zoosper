<?php

declare(strict_types=1);

namespace Zoosper\Media\Controller;

use RuntimeException;
use Throwable;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\AdminForm\AdminFormRegistry;
use Zoosper\AdminForm\AdminFormRenderer;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Message\FlashMessageStoreInterface;
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
        private AdminFormRegistry $formRegistry,
        private AdminFormRenderer $formRenderer,
        private ?AdminUrlGenerator $adminUrls = null,
        private ?MediaLifecycleCoordinator $lifecycle = null,
        private ?MediaVisualGridWorkspace $visualGrid = null,
        private ?FlashMessageStoreInterface $flash = null,
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

        $formDef = $this->formRegistry->get('admin.media.upload.form');
        $formHtml = $this->formRenderer->render(
            $formDef,
            [],
            $this->adminUrl('media/upload'),
            'POST',
            [],
            $this->adminUrl('media'),
            $this->csrf->token()
        );

        return Response::html($this->views->render(
            'Upload media',
            'zoosper-admin::admin/generic/form',
            [
                'formHtml' => $formHtml,
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
        try {
            $successful = match ($operation) {
                'archive' => $this->lifecycle->archive($asset, $user->id, $user->email),
                'restore' => $this->lifecycle->restore($asset, $user->id, $user->email),
                'delete' => $this->lifecycle->deletePermanentlyGuarded($asset, $user->id, $user->email)->successful,
                default => throw new \LogicException('Unsupported Media lifecycle operation.'),
            };
            if ($successful) {
                $this->flash?->success(match ($operation) {
                    'archive' => 'Media asset archived.',
                    'restore' => 'Media asset restored.',
                    'delete' => 'Media asset permanently deleted.',
                }, 'media.lifecycle.' . $operation);
            } else {
                $this->flash?->error(
                    $operation === 'delete'
                        ? 'Media deletion was blocked. Archive it first and remove current Page and restorable revision references.'
                        : 'Media lifecycle operation is not valid for the current status.',
                    'media.lifecycle.blocked',
                );
            }
        } catch (Throwable $exception) {
            $this->flash?->error($exception->getMessage(), 'media.lifecycle.failed');
        }
        return Response::redirect($this->adminUrl('media'), 303);
    }

    private function uploadErrorResponse(AdminUser $user, array $errors, int $status): Response
    {
        $formDef = $this->formRegistry->get('admin.media.upload.form');
        $formHtml = $this->formRenderer->render(
            $formDef,
            [],
            $this->adminUrl('media/upload'),
            'POST',
            ['_form' => implode(' ', $errors)],
            $this->adminUrl('media'),
            $this->csrf->token()
        );

        return Response::html($this->views->render(
            'Upload media',
            'zoosper-admin::admin/generic/form',
            [
                'formHtml' => $formHtml,
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

