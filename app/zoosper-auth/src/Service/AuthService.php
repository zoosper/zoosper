<?php

declare(strict_types=1);

namespace Zoosper\Auth\Service;

use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;

final readonly class AuthService
{
    public function __construct(
        private AdminUserRepository $users,
        private PasswordHasher $hasher,
    ) {
    }

    public function authenticate(string $email, string $password): ?AdminUser
    {
        $user = $this->users->findByEmail($email);

        if ($user === null || !$user->isActive()) {
            return null;
        }

        if (!$this->hasher->verify($password, $user->passwordHash)) {
            return null;
        }

        $this->users->updateLastLogin($user->id);

        return $this->users->findById($user->id);
    }
}
