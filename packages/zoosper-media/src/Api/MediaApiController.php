<?php

declare(strict_types=1);

namespace Zoosper\Media\Api;

use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenPrincipal;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Media\Lifecycle\MediaLifecycleCoordinator;
use Zoosper\Media\Model\MediaAsset;
use Zoosper\Media\Model\MediaDerivative;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Repository\MediaDerivativeRepository;
use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Pagination\PaginationResult;

/** Stateless PAT adapter for feature-owned Media reads, upload, archive and restore. */
final readonly class MediaApiController
{
    public function __construct(
        private JsonResponder $json,
        private PersonalAccessTokenAuthenticator $auth,
        private MediaAssetRepository $assets,
        private MediaDerivativeRepository $derivatives,
        private MediaUploadService $uploads,
        private MediaLifecycleCoordinator $lifecycle,
        private ?AuditLoggerInterface $audit = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $principal = $this->principal($request, 'media:read');
        if ($principal instanceof Response) {
            return $principal;
        }

        $result = $this->assets->paginate(MediaApiReadQuery::fromRequest($request));

        return $this->json->success([
            'media' => array_map($this->normaliseAsset(...), $result->items),
            'pagination' => $this->normalisePagination($result),
        ]);
    }

    public function show(Request $request): Response
    {
        $principal = $this->principal($request, 'media:read');
        if ($principal instanceof Response) {
            return $principal;
        }
        $asset = $this->asset($request);
        if ($asset === null) {
            return $this->json->error('media_not_found', 'Media asset does not exist.', 404);
        }

        return $this->json->success(['media' => $this->normaliseAsset($asset)]);
    }

    public function derivatives(Request $request): Response
    {
        $principal = $this->principal($request, 'media:read');
        if ($principal instanceof Response) {
            return $principal;
        }
        $asset = $this->asset($request);
        if ($asset === null) {
            return $this->json->error('media_not_found', 'Media asset does not exist.', 404);
        }

        return $this->json->success([
            'media_id' => $asset->id,
            'derivatives' => array_map($this->normaliseDerivative(...), $this->derivatives->forAsset($asset->id)),
        ]);
    }

    public function upload(Request $request): Response
    {
        $principal = $this->principal($request, 'media:upload');
        if ($principal instanceof Response) {
            return $principal;
        }
        $file = $request->uploadedFile('file');
        $result = $this->uploads->upload($file, $principal->user);
        if (!$result->successful) {
            return $this->json->error('media_upload_failed', $result->message, $result->statusCode);
        }
        $asset = $result->assetId === null ? null : $this->assets->findById($result->assetId);
        if ($asset === null) {
            return $this->json->error('media_reload_failed', 'Uploaded Media could not be reloaded.', 500);
        }
        $this->audit?->logAction(
            $principal->user->id,
            $principal->user->email,
            'media.api_uploaded',
            'media_asset',
            (string) $asset->id,
            'media.api_uploaded',
            ['asset_id' => $asset->id, 'token_id' => $principal->token->id, 'token_public_id' => $principal->token->publicId]
        );

        return $this->json->success([
            'media' => $this->normaliseAsset($asset),
            'derivatives' => array_map($this->normaliseDerivative(...), $this->derivatives->forAsset($asset->id)),
        ], 201);
    }

    public function archive(Request $request): Response
    {
        return $this->lifecycle($request, 'archive');
    }

    public function restore(Request $request): Response
    {
        return $this->lifecycle($request, 'restore');
    }

    public function deletePermanently(Request $request): Response
    {
        $principal = $this->principal($request, 'media:delete');
        if ($principal instanceof Response) {
            return $principal;
        }
        $asset = $this->asset($request);
        if ($asset === null) {
            return $this->json->error('media_not_found', 'Media asset does not exist.', 404);
        }
        $result = $this->lifecycle->deletePermanentlyGuarded($asset, $principal->user->id, $principal->user->email);
        if (!$result->successful) {
            return $this->json->error('media_delete_blocked', $result->message ?? 'Media deletion was blocked.', 409, ['blockers' => $result->blockers]);
        }

        return $this->json->success(['deleted' => true, 'media_id' => $asset->id]);
    }

    private function lifecycle(Request $request, string $operation): Response
    {
        $principal = $this->principal($request, 'media:delete');
        if ($principal instanceof Response) {
            return $principal;
        }
        $asset = $this->asset($request);
        if ($asset === null) {
            return $this->json->error('media_not_found', 'Media asset does not exist.', 404);
        }
        $successful = $operation === 'archive'
            ? $this->lifecycle->archive($asset, $principal->user->id, $principal->user->email)
            : $this->lifecycle->restore($asset, $principal->user->id, $principal->user->email);
        if (!$successful) {
            return $this->json->error('media_lifecycle_blocked', 'Media lifecycle operation is not valid for the current status.', 409);
        }
        $updated = $this->assets->findById($asset->id);

        return $this->json->success(['media' => $this->normaliseAsset($updated ?? $asset)]);
    }

    private function principal(Request $request, string $scope): PersonalAccessTokenPrincipal|Response
    {
        $principal = $this->auth->authenticate($request->header('authorization'));
        if ($principal === null) {
            return $this->json->error('invalid_bearer_token', 'A valid bearer token is required.', 401);
        }
        if (!$principal->allows($scope) || !$principal->user->can('media.manage')) {
            return $this->json->error('insufficient_scope', 'The bearer token cannot perform this Media operation.', 403);
        }

        return $principal;
    }

    private function asset(Request $request): ?MediaAsset
    {
        return $this->assets->findById((int) $request->routeParam('id', '0'));
    }

    /** @return array<string, mixed> */
    private function normaliseAsset(MediaAsset $asset): array
    {
        return [
            'id' => $asset->id,
            'uuid' => $asset->uuid,
            'filename' => $asset->filename,
            'original_filename' => $asset->originalFilename,
            'mime_type' => $asset->mimeType,
            'extension' => $asset->extension,
            'size_bytes' => $asset->sizeBytes,
            'public_path' => $asset->publicPath,
            'status' => $asset->status,
            'created_by' => $asset->createdBy,
            'created_at' => $asset->createdAt,
            'updated_at' => $asset->updatedAt,
        ];
    }

    /** @param PaginationResult<MediaAsset> $result @return array<string, int|bool> */
    private function normalisePagination(PaginationResult $result): array
    {
        return [
            'page' => $result->page,
            'page_size' => $result->pageSize,
            'page_count' => $result->totalPages(),
            'total' => $result->total,
            'has_previous' => $result->hasPrevious(),
            'has_next' => $result->hasNext(),
        ];
    }

    /** @return array<string, mixed> */
    private function normaliseDerivative(MediaDerivative $derivative): array
    {
        return [
            'id' => $derivative->id,
            'media_id' => $derivative->mediaAssetId,
            'profile' => $derivative->profile,
            'format' => $derivative->format,
            'width' => $derivative->width,
            'height' => $derivative->height,
            'size_bytes' => $derivative->sizeBytes,
            'public_path' => $derivative->publicPath,
            'created_at' => $derivative->createdAt,
            'updated_at' => $derivative->updatedAt,
        ];
    }
}
