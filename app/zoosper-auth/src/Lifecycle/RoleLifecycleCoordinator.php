<?php

declare(strict_types=1);

namespace Zoosper\Auth\Lifecycle;

use PDO;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Audit\Contract\AuditLoggerInterface;

/** Guards permanent deletion of unassigned, non-system Admin roles. */
final readonly class RoleLifecycleCoordinator
{
    private const PROTECTED_CODES = ['super_admin'];

    public function __construct(private PDO $pdo, private ?AuditLoggerInterface $audit = null)
    {
    }

    public function deletePermanently(int $roleId, string $roleCode, AdminUser $actor): RoleLifecycleResult
    {
        if (in_array($roleCode, self::PROTECTED_CODES, true)) {
            return RoleLifecycleResult::denied('System Roles cannot be deleted.');
        }
        $assignments = $this->assignmentCount($roleId);
        if ($assignments > 0) {
            return RoleLifecycleResult::denied('Remove Admin User assignments before deleting this Role.', $assignments);
        }
        $ownsTransaction = !$this->pdo->inTransaction();
        if ($ownsTransaction) {
            $this->pdo->beginTransaction();
        }
        try {
            $permissions = $this->pdo->prepare('DELETE FROM admin_role_permissions WHERE role_id = :role_id');
            $permissions->execute(['role_id' => $roleId]);
            $role = $this->pdo->prepare('DELETE FROM admin_roles WHERE id = :id');
            $role->execute(['id' => $roleId]);
            if ($role->rowCount() !== 1) {
                throw new \RuntimeException('Role permanent deletion did not remove exactly one row.');
            }
            if ($ownsTransaction) {
                $this->pdo->commit();
            }
        } catch (\Throwable $exception) {
            if ($ownsTransaction && $this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
        $this->audit?->logAction($actor->id, $actor->email, 'admin_role.deleted', 'admin_role', (string) $roleId, 'Permanently deleted Admin Role.', ['role_code' => $roleCode]);
        return RoleLifecycleResult::success('Role permanently deleted.');
    }

    public function assignmentCount(int $roleId): int
    {
        $statement = $this->pdo->prepare('SELECT COUNT(*) FROM admin_user_roles WHERE role_id = :role_id');
        $statement->execute(['role_id' => $roleId]);
        return (int) $statement->fetchColumn();
    }
}










