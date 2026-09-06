<?php

declare(strict_types=1);

use PDO;
use Zoosper\Admin\Controller\PasswordResetController;
use Zoosper\Auth\Http\AuthenticationMiddleware;
use Zoosper\Auth\Http\CsrfMiddleware;
use Zoosper\Auth\PasswordReset\AdminPasswordResetService;
use Zoosper\Auth\RateLimit\AdminAuthenticationRateLimiterInterface;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\AuthService;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Core\Bootstrap\ApplicationFactory;
use Zoosper\Core\Http\Middleware\MiddlewarePipeline;
use Zoosper\Core\Http\Middleware\RouteContext;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Http\Response;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Routing\Router;
use Zoosper\Database\Migrator;

/**
 * Boots the real service graph and exercises the four public reset routes through
 * the same Router, authentication and CSRF middleware contracts used at runtime.
 */
it('accepts the complete public Admin password reset HTTP lifecycle', function (): void {
    $root = dirname(__DIR__, 4);
    if (!function_exists('env')) {
        require_once $root . '/bootstrap/autoload.php';
    }

    $environment = [
        'APP_ENV' => 'testing',
        'DB_CONNECTION' => 'sqlite',
        'DB_DRIVER' => 'sqlite',
        'DB_DATABASE' => ':memory:',
        'RATE_LIMIT_ENABLED' => 'true',
        'RATE_LIMIT_MODE' => 'enforce',
        'RATE_LIMIT_IDENTITY_SALT' => str_repeat('c', 64),
        'RATE_LIMIT_ADMIN_PASSWORD_RESET_MAX_ATTEMPTS' => '1',
        'RATE_LIMIT_ADMIN_PASSWORD_RESET_WINDOW_SECONDS' => '900',
    ];
    foreach ($environment as $key => $value) {
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
    $oldPassword = 'InitialPassword123!';
    $newPassword = 'ReplacementPassword456!';
    $userId = $users->createWithRoleIds('admin@example.test', 'Admin', $hasher->hash($oldPassword), 'active', [1]);
    $users->createWithRoleIds('inactive@example.test', 'Inactive', $hasher->hash($oldPassword), 'inactive', [1]);

    $controller = $services->get(PasswordResetController::class);
    $pipeline = new MiddlewarePipeline([
        $services->get(AuthenticationMiddleware::class),
        $services->get(CsrfMiddleware::class),
    ]);
    $router = new Router();
    $routes = [
        ['GET', '/admin/forgot-password', 'forgotForm'],
        ['POST', '/admin/forgot-password', 'requestReset'],
        ['GET', '/admin/reset-password', 'resetForm'],
        ['POST', '/admin/reset-password', 'resetPassword'],
    ];
    foreach ($routes as [$method, $path, $action]) {
        $context = new RouteContext($method, $path, isPublic: true);
        $router->map($method, $path, static fn (Request $request): Response => $pipeline->handle(
            $request,
            $context,
            static fn (Request $accepted): Response => $controller->{$action}($accepted),
        ));
    }

    $forgot = $router->dispatch(new Request('GET', '/admin/forgot-password'));
    expect($forgot->statusCode())->toBe(200)
        ->and($forgot->headers()['Content-Type'])->toBe('text/html; charset=utf-8')
        ->and($forgot->body())->toContain('Forgot password')
        ->toContain('name="_csrf_token"')
        ->toContain('noindex,nofollow');

    $blocked = $router->dispatch(new Request('POST', '/admin/forgot-password', form: [
        'email' => 'missing@example.test',
    ]));
    expect($blocked->statusCode())->toBe(419)
        ->and($blocked->body())->toContain('session security token expired')
        ->and((int) $pdo->query('SELECT COUNT(*) FROM admin_password_reset_tokens')->fetchColumn())->toBe(0);

    $csrf = $services->get(CsrfTokenManager::class);
    $csrfToken = $csrf->token();
    $unknown = $router->dispatch(new Request('POST', '/admin/forgot-password', clientIp: '203.0.113.20', form: [
        '_csrf_token' => $csrfToken,
        'email' => 'missing@example.test',
    ]));
    $inactive = $router->dispatch(new Request('POST', '/admin/forgot-password', clientIp: '203.0.113.21', form: [
        '_csrf_token' => $csrfToken,
        'email' => 'inactive@example.test',
    ]));
    expect($unknown->statusCode())->toBe(200)
        ->and($inactive->statusCode())->toBe(200)
        ->and($unknown->body())->toBe($inactive->body())
        ->and($unknown->body())->toContain('If an active Admin account matches that email')
        ->and((int) $pdo->query('SELECT COUNT(*) FROM admin_password_reset_tokens')->fetchColumn())->toBe(0);

    $resets = $services->get(AdminPasswordResetService::class);
    $outstanding = $resets->issueForEmail('admin@example.test');
    expect($outstanding)->not->toBeNull();
    $beforeId = (int) $pdo->query('SELECT id FROM admin_password_reset_tokens WHERE consumed_at IS NULL')->fetchColumn();

    $limiter = $services->get(AdminAuthenticationRateLimiterInterface::class);
    $limiter->checkPasswordResetRequest('admin@example.test', '203.0.113.22');
    $denied = $router->dispatch(new Request('POST', '/admin/forgot-password', clientIp: '203.0.113.22', form: [
        '_csrf_token' => $csrfToken,
        'email' => 'admin@example.test',
    ]));
    expect($denied->statusCode())->toBe(200)
        ->and($denied->body())->toBe($unknown->body())
        ->and((int) $pdo->query('SELECT COUNT(*) FROM admin_password_reset_tokens')->fetchColumn())->toBe(1)
        ->and((int) $pdo->query('SELECT id FROM admin_password_reset_tokens WHERE consumed_at IS NULL')->fetchColumn())->toBe($beforeId);

    $resetForm = $router->dispatch(new Request('GET', '/admin/reset-password', query: ['token' => $outstanding->token]));
    expect($resetForm->statusCode())->toBe(200)
        ->and($resetForm->body())->toContain('Choose a new password')
        ->toContain('value="' . $outstanding->token . '"')
        ->toContain('autocomplete="new-password"');

    $mismatch = $router->dispatch(new Request('POST', '/admin/reset-password', form: [
        '_csrf_token' => $csrfToken,
        'token' => $outstanding->token,
        'password' => $newPassword,
        'password_confirmation' => 'DifferentPassword789!',
    ]));
    expect($mismatch->statusCode())->toBe(422)
        ->and($mismatch->body())->toContain('role="alert"')
        ->and($mismatch->body())->toContain('value="' . $outstanding->token . '"');

    $_SESSION['admin_user_id'] = $userId;
    $_SESSION['admin_password_hash_fingerprint'] = hash('sha256', $users->findById($userId)?->passwordHash ?? '');
    $_SESSION['admin_last_activity_at'] = time();
    $oldCsrf = $csrf->token();
    $completed = $router->dispatch(new Request('POST', '/admin/reset-password', form: [
        '_csrf_token' => $oldCsrf,
        'token' => $outstanding->token,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]));
    expect($completed->statusCode())->toBe(303)
        ->and($completed->headers()['Location'])->toBe('/admin/login?reset=complete')
        ->and($csrf->isValid($oldCsrf))->toBeFalse()
        ->and((int) $pdo->query('SELECT COUNT(*) FROM admin_password_reset_tokens WHERE consumed_at IS NOT NULL')->fetchColumn())->toBe(1);

    $auth = $services->get(AuthService::class);
    expect($auth->authenticate('admin@example.test', $oldPassword))->toBeNull()
        ->and($auth->authenticate('admin@example.test', $newPassword))->not->toBeNull()
        ->and((new SessionGuard($users))->user())->toBeNull();

    $reused = $router->dispatch(new Request('POST', '/admin/reset-password', form: [
        '_csrf_token' => $csrf->token(),
        'token' => $outstanding->token,
        'password' => 'AnotherPassword789!',
        'password_confirmation' => 'AnotherPassword789!',
    ]));
    expect($reused->statusCode())->toBe(422);

    $_SESSION = [];
    foreach (array_keys($environment) as $key) {
        unset($_ENV[$key]);
        putenv($key);
    }
});
