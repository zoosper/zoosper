<?php

declare(strict_types=1);

namespace Zoosper\Auth\Console;

use RuntimeException;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Security\PasswordPolicy;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Core\Console\ConsoleCommandInterface;
use Zoosper\Core\Console\ConsoleOptions;
use Zoosper\Core\Console\ConsoleOutput;

/**
 * `admin:create` — creates a super-admin user.
 *
 * Console/kernel decoupling phase: relocated out of bin/zoosper, which
 * previously imported AdminUserRepository and PasswordHasher directly. The
 * kernel now discovers this command through ModuleConsoleCommandLoader
 * (config/console.php + config/services.php), the same mechanism any
 * third-party module would use.
 */
final readonly class AdminCreateCommand implements ConsoleCommandInterface
{
    public function __construct(
        private AdminUserRepository $users,
        private PasswordHasher $hasher,
        private ?PasswordPolicy $passwordPolicy = null,
    ) {
    }

    public function name(): string
    {
        return 'admin:create';
    }

    public function description(): string
    {
        return "--email=admin@example.com --password='ChangeMe123!' --name='Admin User'";
    }

    public function run(array $args, ConsoleOutput $output): int
    {
        $options = ConsoleOptions::parse($args);

        try {
            $email = ConsoleOptions::required($options, 'email');
            $password = ConsoleOptions::required($options, 'password');
        } catch (RuntimeException $exception) {
            $output->errorln($exception->getMessage());
            return 1;
        }

        $policy = $this->passwordPolicy ?? new PasswordPolicy();
        $violations = $policy->violations($password);
        if ($violations !== []) {
            $output->errorln($violations[0]);
            return 1;
        }

        $name = $options['name'] ?? 'Admin User';

        if ($this->users->findByEmail($email) !== null) {
            $output->errorln("Admin user already exists: {$email}");
            return 1;
        }

        $id = $this->users->create($email, $name, $this->hasher->hash($password), 'super_admin');
        $output->writeln("Created super admin user #{$id}: {$email}");

        return 0;
    }
}










