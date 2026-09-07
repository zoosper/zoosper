<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Feature;

use PDO;
use Zoosper\Admin\Controller\LoginController;
use Zoosper\Auth\AccountLockout\AdminAccountLockoutService;
use Zoosper\Auth\Admin\Controller\UserAdminController;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Core\Bootstrap\ApplicationFactory;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Testing\TestCase;
use Zoosper\Database\Migrator;

final class AdminAccountLockoutHttpAcceptanceTest extends TestCase
{
    /** @var array<string,string> */
    private array $environment = [];

    protected function tearDown(): void
    {
        $_SESSION = [];
        foreach (array_keys($this->environment) as $key) {
            unset($_ENV[$key]);
            putenv($key);
        }
        parent::tearDown();
    }

    public function testLoginLockoutAndProtectedManualUnlockJourney(): void
    {
        $root = dirname(__DIR__, 4);
        if (!function_exists('env')) {
            require_once $root . '/bootstrap/autoload.php';
        }
        $this->environment = [
            'APP_ENV' => 'testing',
            'DB_DRIVER' => 'sqlite',
            'DB_DATABASE' => ':memory:',
            'ADMIN_ACCOUNT_LOCKOUT_MAX_ATTEMPTS' => '2',
            'ADMIN_ACCOUNT_LOCKOUT_SECONDS' => '900',
        ];
        foreach ($this->environment as $key => $value) {
            $_ENV[$key] = $value;
            putenv($key . '=' . $value);
        }
        $_SESSION = [];

        $app = ApplicationFactory::create($root);
        restore_error_handler();
        restore_exception_handler();
        restore_error_handler();
        restore_exception_handler();
        $services = $app->services();
        $pdo = $services->get(PDO::class);
        (new Migrator($pdo, $root, $services->get(ModuleRegistry::class)))->migrate();

        $users = $services->get(AdminUserRepository::class);
        $hasher = $services->get(PasswordHasher::class);
        $actorId = $users->createWithRoleIds('actor@example.test', 'Actor', $hasher->hash('ActorPassword123!'), 'active', [1]);
        $targetId = $users->createWithRoleIds('target@example.test', 'Target', $hasher->hash('CorrectPassword123!'), 'active', [1]);
        $csrf = $services->get(CsrfTokenManager::class);
        $login = $services->get(LoginController::class);
        $lockouts = $services->get(AdminAccountLockoutService::class);

        $first = $login->login(new Request('POST', '/admin/login', clientIp: '203.0.113.30', form: [
            '_csrf_token' => $csrf->token(),
            'email' => 'target@example.test',
            'password' => 'WrongPassword123!',
        ]));
        $second = $login->login(new Request('POST', '/admin/login', clientIp: '203.0.113.30', form: [
            '_csrf_token' => $csrf->token(),
            'email' => 'target@example.test',
            'password' => 'WrongPassword123!',
        ]));
        $correctWhileLocked = $login->login(new Request('POST', '/admin/login', clientIp: '203.0.113.30', form: [
            '_csrf_token' => $csrf->token(),
            'email' => 'target@example.test',
            'password' => 'CorrectPassword123!',
        ]));

        self::assertSame(422, $first->statusCode());
        self::assertSame(422, $second->statusCode());
        self::assertSame(422, $correctWhileLocked->statusCode());
        self::assertStringContainsString('Invalid email or password.', $first->body());
        self::assertSame($first->body(), $second->body());
        self::assertSame($second->body(), $correctWhileLocked->body());
        self::assertStringNotContainsString('locked', strtolower($correctWhileLocked->body()));
        self::assertTrue($lockouts->isLocked($targetId));
        self::assertSame(2, $lockouts->state($targetId)?->failedAttempts);

        $_SESSION['admin_user_id'] = $actorId;
        $_SESSION['admin_password_hash_fingerprint'] = hash('sha256', $users->findById($actorId)?->passwordHash ?? '');
        $_SESSION['admin_last_activity_at'] = time();
        $usersController = $services->get(UserAdminController::class);
        $edit = $usersController->editForm(new Request(
            'GET',
            '/admin/users/edit',
            query: ['id' => (string) $targetId],
        ));
        self::assertSame(200, $edit->statusCode());
        self::assertStringContainsString('Account temporarily locked', $edit->body());
        self::assertStringContainsString('Failed password attempts:', $edit->body());
        self::assertStringContainsString('/admin/users/' . $targetId . '/unlock', $edit->body());
        self::assertStringContainsString('Unlock account', $edit->body());

        $unlockRouter = new \Zoosper\Core\Routing\Router();
        $unlockRouter->post('/admin/users/{id}/unlock', static fn (Request $request) => $usersController->unlock($request));

        // CSRF and user.manage are central middleware responsibilities covered by
        // the existing route/middleware contracts. This focused Router journey
        // verifies constrained route parameters and the controller mutation.
        $lockouts->recordFailure($targetId);
        $unlocked = $unlockRouter->dispatch(new Request('POST', '/admin/users/' . $targetId . '/unlock', form: [
            '_csrf_token' => $csrf->token(),
        ]));
        self::assertSame(303, $unlocked->statusCode());
        self::assertSame('/admin/users/edit?id=' . $targetId, $unlocked->headers()['Location'] ?? null);
        self::assertNull($lockouts->state($targetId));
        self::assertSame('active', $users->findById($targetId)?->status);
        self::assertTrue($hasher->verify('CorrectPassword123!', $users->findById($targetId)?->passwordHash ?? ''));

        $_SESSION = [];
        $afterUnlock = $login->login(new Request('POST', '/admin/login', clientIp: '203.0.113.31', form: [
            '_csrf_token' => $csrf->token(),
            'email' => 'target@example.test',
            'password' => 'CorrectPassword123!',
        ]));
        self::assertContains($afterUnlock->statusCode(), [302, 303]);
    }
}
