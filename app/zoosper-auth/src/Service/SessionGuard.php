<?php

declare(strict_types=1);

namespace Zoosper\Auth\Service;

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;

final readonly class SessionGuard
{
    public function __construct(private AdminUserRepository $users)
    {
    }

    public function login(AdminUser $user): void
    {
        session_regenerate_id(true);
        $_SESSION['admin_user_id'] = $user->id;
    }

    public function logout(): void
    {
        $_SESSION = [];

        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }

    public function user(): ?AdminUser
    {
        $id = $_SESSION['admin_user_id'] ?? null;

        return is_numeric($id) ? $this->users->findById((int) $id) : null;
    }

    public function requirePermission(string $permission): ?AdminUser
    {
        $user = $this->user();

        if ($user === null || !$user->can($permission)) {
            return null;
        }

        return $user;
    }
}
