<?php

declare(strict_types=1);

namespace Zoosper\Auth\Api;

use RuntimeException;
use Zoosper\Auth\Repository\RoleRepository;
use Zoosper\Auth\Token\PersonalAccessTokenAuthenticator;
use Zoosper\Auth\Token\PersonalAccessTokenPrincipal;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Core\Http\JsonResponder;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;

final readonly class RoleApiController
{
    public function __construct(
        private JsonResponder $json,
        private PersonalAccessTokenAuthenticator $auth,
        private RoleRepository $roles,
        private ?AuditLoggerInterface $audit = null,
    ) {
    }

    public function index(Request $request): Response
    {
        $principal = $this->principal($request, 'roles:read', true);
        if ($principal instanceof Response) {
            return $principal;
        }

        $roles = array_map(function (array $role): array {
            $roleId = (int) $role['id'];
            return [
                'id' => $roleId,
                'code' => (string) ($role['code'] ?? ''),
                'label' => (string) ($role['label'] ?? ''),
                'permission_ids' => $this->roles->permissionIdsForRole($roleId),
                'user_ids' => $this->roles->userIdsForRole($roleId),
                'created_at' => $role['created_at'] ?? null,
                'updated_at' => $role['updated_at'] ?? null,
            ];
        }, $this->roles->allRoles());

        return $this->json->success(['roles' => $roles]);
    }

    public function show(Request $request): Response
    {
        $principal = $this->principal($request, 'roles:read', true);
        if ($principal instanceof Response) {
            return $principal;
        }

        $id = (int) $request->routeParam('id', '0');
        $role = $this->roles->findRoleById($id);
        if ($role === null) {
            return $this->json->error('role_not_found', 'Role not found.', 404);
        }

        return $this->json->success([
            'role' => [
                'id' => (int) $role['id'],
                'code' => (string) ($role['code'] ?? ''),
                'label' => (string) ($role['label'] ?? ''),
                'permission_ids' => $this->roles->permissionIdsForRole((int) $role['id']),
                'user_ids' => $this->roles->userIdsForRole((int) $role['id']),
                'created_at' => $role['created_at'] ?? null,
                'updated_at' => $role['updated_at'] ?? null,
            ],
        ]);
    }

    public function permissions(Request $request): Response
    {
        $principal = $this->principal($request, 'roles:read', true);
        if ($principal instanceof Response) {
            return $principal;
        }

        return $this->json->success([
            'permissions' => $this->roles->allPermissions(),
        ]);
    }

    public function create(Request $request): Response
    {
        $principal = $this->principal($request, 'roles:write');
        if ($principal instanceof Response) {
            return $principal;
        }

        $body = $request->json();
        $code = trim((string) ($body['code'] ?? ''));
        $label = trim((string) ($body['label'] ?? ''));
        $permissionIds = is_array($body['permission_ids'] ?? null)
            ? array_values(array_map('intval', $body['permission_ids']))
            : [];

        if ($code === '' || $label === '') {
            return $this->json->error('role_validation_failed', 'Both code and label are required.', 422);
        }

        try {
            $id = $this->roles->createRole($code, $label, $permissionIds);
            $created = $this->roles->findRoleById($id);

            $this->audit?->logAction(
                $principal->user->id,
                $principal->user->email,
                'role.api_created',
                'admin_role',
                (string) $id,
                'Created admin role via API',
                ['code' => $code, 'label' => $label, 'permission_ids' => $permissionIds],
            );

            return $this->json->success([
                'role' => [
                    'id' => $id,
                    'code' => (string) ($created['code'] ?? $code),
                    'label' => (string) ($created['label'] ?? $label),
                    'permission_ids' => $this->roles->permissionIdsForRole($id),
                    'user_ids' => $this->roles->userIdsForRole($id),
                    'created_at' => $created['created_at'] ?? gmdate('Y-m-d H:i:s'),
                    'updated_at' => $created['updated_at'] ?? gmdate('Y-m-d H:i:s'),
                ],
            ], 201);
        } catch (RuntimeException $exception) {
            return $this->json->error('role_creation_failed', $exception->getMessage(), 422);
        }
    }

    public function update(Request $request): Response
    {
        $principal = $this->principal($request, 'roles:write');
        if ($principal instanceof Response) {
            return $principal;
        }

        $id = (int) $request->routeParam('id', '0');
        $role = $this->roles->findRoleById($id);
        if ($role === null) {
            return $this->json->error('role_not_found', 'Role not found.', 404);
        }

        $body = $request->json();
        $code = isset($body['code']) ? trim((string) $body['code']) : (string) $role['code'];
        $label = isset($body['label']) ? trim((string) $body['label']) : (string) $role['label'];
        $permissionIds = isset($body['permission_ids']) && is_array($body['permission_ids'])
            ? array_values(array_map('intval', $body['permission_ids']))
            : $this->roles->permissionIdsForRole($id);
        $userIds = isset($body['user_ids']) && is_array($body['user_ids'])
            ? array_values(array_map('intval', $body['user_ids']))
            : null;

        if ($code === '' || $label === '') {
            return $this->json->error('role_validation_failed', 'Both code and label are required.', 422);
        }

        try {
            $this->roles->updateRole($id, $code, $label, $permissionIds, $userIds);
            $updated = $this->roles->findRoleById($id);

            $this->audit?->logAction(
                $principal->user->id,
                $principal->user->email,
                'role.api_updated',
                'admin_role',
                (string) $id,
                'Updated admin role via API',
                ['code' => $code, 'label' => $label, 'permission_ids' => $permissionIds, 'user_ids' => $userIds],
            );

            return $this->json->success([
                'role' => [
                    'id' => $id,
                    'code' => (string) ($updated['code'] ?? $code),
                    'label' => (string) ($updated['label'] ?? $label),
                    'permission_ids' => $this->roles->permissionIdsForRole($id),
                    'user_ids' => $this->roles->userIdsForRole($id),
                    'created_at' => $updated['created_at'] ?? null,
                    'updated_at' => $updated['updated_at'] ?? null,
                ],
            ]);
        } catch (RuntimeException $exception) {
            return $this->json->error('role_update_failed', $exception->getMessage(), 422);
        }
    }

    public function delete(Request $request): Response
    {
        $principal = $this->principal($request, 'roles:write');
        if ($principal instanceof Response) {
            return $principal;
        }

        $id = (int) $request->routeParam('id', '0');
        $role = $this->roles->findRoleById($id);
        if ($role === null) {
            return $this->json->error('role_not_found', 'Role not found.', 404);
        }

        $deleted = $this->roles->deleteRole($id);
        if (!$deleted) {
            return $this->json->error('role_delete_failed', 'Role could not be deleted.', 500);
        }

        $this->audit?->logAction(
            $principal->user->id,
            $principal->user->email,
            'role.api_deleted',
            'admin_role',
            (string) $id,
            'Deleted admin role via API',
            ['code' => (string) ($role['code'] ?? '')],
        );

        return $this->json->success(['deleted' => true, 'id' => $id]);
    }

    private function principal(Request $request, string $scope, bool $read = false): PersonalAccessTokenPrincipal|Response
    {
        $p = $this->auth->authenticate($request->header('authorization'));
        if ($p === null) {
            return $this->json->error('invalid_bearer_token', 'A valid bearer token is required.', 401);
        }

        $allowed = $read ? ($p->user->can('role.view') || $p->user->can('role.manage')) : $p->user->can('role.manage');
        if (!$p->allows($scope) || !$allowed) {
            return $this->json->error('insufficient_scope', 'The bearer token cannot perform this Role operation.', 403);
        }

        return $p;
    }
}
