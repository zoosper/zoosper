<?php

declare(strict_types=1);

namespace Zoosper\Auth\Lifecycle;

use PDO;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Audit\Contract\AuditLoggerInterface;

/** Applies reversible Admin-user status changes without deleting identity history. */
final readonly class AdminUserLifecycleCoordinator
{
    public function __construct(
        private PDO $pdo,
        private AdminUserRepository $users,
        private ?AuditLoggerInterface $audit = null,
    ) {
    }

    public function disable(AdminUser $target, AdminUser $actor): AdminUserLifecycleResult
    {
        if ($target->id === $actor->id) {
            return AdminUserLifecycleResult::denied('You cannot disable the currently authenticated Admin account.');
        }
        if ($target->status === 'inactive') {
            return AdminUserLifecycleResult::success('Admin User is already inactive.');
        }
        if ($this->isSuperAdmin($target->id) && $this->activeSuperAdminCount() <= 1) {
            return AdminUserLifecycleResult::denied('The last active super administrator cannot be disabled.');
        }
        $this->users->updateStatus($target->id, 'inactive');
        $this->audit?->logAction($actor->id, $actor->email, 'admin_user.disabled', 'admin_user', (string) $target->id, 'Disabled Admin User.', ['target_email' => $target->email]);
        return AdminUserLifecycleResult::success('Admin User made inactive.');
    }

    public function restore(AdminUser $target, AdminUser $actor): AdminUserLifecycleResult
    {
        if ($target->status === 'active') {
            return AdminUserLifecycleResult::success('Admin User is already active.');
        }
        $this->users->updateStatus($target->id, 'active');
        $this->audit?->logAction($actor->id, $actor->email, 'admin_user.restored', 'admin_user', (string) $target->id, 'Restored Admin User.', ['target_email' => $target->email]);
        return AdminUserLifecycleResult::success('Admin User restored.');
    }

    private function isSuperAdmin(int $userId): bool
    {
        $statement = $this->pdo->prepare("SELECT COUNT(*) FROM admin_user_roles ur INNER JOIN admin_roles r ON r.id = ur.role_id WHERE ur.user_id = :user_id AND r.code = 'super_admin'");
        $statement->execute(['user_id' => $userId]);
        return (int) $statement->fetchColumn() > 0;
    }

    private function activeSuperAdminCount(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(DISTINCT u.id) FROM admin_users u INNER JOIN admin_user_roles ur ON ur.user_id = u.id INNER JOIN admin_roles r ON r.id = ur.role_id WHERE u.status = 'active' AND r.code = 'super_admin'")->fetchColumn();
    }
}










