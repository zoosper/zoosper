<?php
declare(strict_types=1);
namespace Zoosper\Auth\Service;
use Zoosper\Auth\Model\AdminUser; use Zoosper\Auth\Repository\AdminUserRepository;
final readonly class AuthService { public function __construct(private AdminUserRepository $users, private PasswordHasher $hasher){} public function authenticate(string $email,string $password):?AdminUser{$u=$this->users->findByEmail($email); if($u===null||!$u->isActive()||!$this->hasher->verify($password,$u->passwordHash)) return null; $this->users->updateLastLogin($u->id); return $this->users->findById($u->id);} }
