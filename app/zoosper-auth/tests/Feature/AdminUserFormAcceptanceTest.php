<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Feature;

use Zoosper\Core\Bootstrap\ApplicationFactory;
use Zoosper\Auth\Admin\Controller\UserAdminController;
use Zoosper\Core\Testing\TestCase;
use Zoosper\Core\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Http\Request;

class AdminUserFormAcceptanceTest extends TestCase
{
    private function bootApp(): \Zoosper\Core\Http\Application
    {
        $basePath = dirname(__DIR__, 4);
        
        if (!function_exists('env')) {
            require_once $basePath . '/bootstrap/autoload.php';
        }

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['APP_DEBUG'] = 'true';

        $app = ApplicationFactory::create($basePath);

        restore_error_handler();
        restore_exception_handler();
        restore_error_handler();
        restore_exception_handler();

        return $app;
    }

    public function testAdminUserCreateFormRendersUnifiedLayout(): void
    {
        $basePath = dirname(__DIR__, 4);
        $app = $this->bootApp();
        $container = $app->services();

        $migrator = new Migrator(
            $container->get(\PDO::class),
            $basePath,
            $container->get(ModuleRegistry::class)
        );
        $migrator->migrate();

        // Seed an admin user to act as actor
        $container->get(\PDO::class)->exec("INSERT INTO admin_users (id, email, name, password_hash, status, created_at, updated_at) VALUES (1, 'admin@example.com', 'Admin', 'hash', 'active', '2026-01-01', '2026-01-01')");
        
        // Mock session for authentication
        $_SESSION['admin_user_id'] = 1;
        $_SESSION['admin_password_hash_fingerprint'] = hash('sha256', 'hash');
        $_SESSION['admin_last_activity_at'] = time();

        /** @var UserAdminController $controller */
        $controller = $container->get(UserAdminController::class);
        
        $request = Request::fromGlobals();
        $response = $controller->createForm($request);
        
        $html = $response->body();

        // Verify unified form markers
        $this->assertStringContainsString('class="admin-user-workspace"', $html);
        $this->assertStringContainsString('class="admin-form"', $html);
        $this->assertStringContainsString('class="card admin-form-section"', $html);
        $this->assertStringContainsString('Identity', $html);
        $this->assertStringContainsString('Admin preferences', $html);
        $this->assertStringContainsString('Assigned roles', $html);
        
        // Verify CSRF token
        $this->assertStringContainsString('name="_csrf_token"', $html);
        
        // Verify dynamic roles section
        $this->assertStringContainsString('class="checkbox-list"', $html);
    }
    
    public function testAdminUserEditFormRendersDangerZone(): void
    {
        $basePath = dirname(__DIR__, 4);
        $app = $this->bootApp();
        $container = $app->services();

        $migrator = new Migrator(
            $container->get(\PDO::class),
            $basePath,
            $container->get(ModuleRegistry::class)
        );
        $migrator->migrate();

        // Seed actor (id=1) and target user (id=2)
        $pdo = $container->get(\PDO::class);
        $pdo->exec("INSERT INTO admin_users (id, email, name, password_hash, status, created_at, updated_at) VALUES (1, 'admin@example.com', 'Admin', 'hash', 'active', '2026-01-01', '2026-01-01')");
        $pdo->exec("INSERT INTO admin_users (id, email, name, password_hash, status, created_at, updated_at) VALUES (2, 'user2@example.com', 'User 2', 'hash', 'active', '2026-01-01', '2026-01-01')");
        
        $_SESSION['admin_user_id'] = 1;
        $_SESSION['admin_password_hash_fingerprint'] = hash('sha256', 'hash');
        $_SESSION['admin_last_activity_at'] = time();

        /** @var UserAdminController $controller */
        $controller = $container->get(UserAdminController::class);
        
        // Mock request with ?id=2
        $_SERVER['REQUEST_URI'] = '/admin/users/edit?id=2';
        $request = Request::fromGlobals();
        $response = $controller->editForm($request);
        
        $html = $response->body();

        // Verify Danger Zone
        $this->assertStringContainsString('Danger Zone', $html);
        $this->assertStringContainsString('Delete admin user', $html);
        $this->assertStringContainsString('/admin/users/2/delete', $html);
    }
}
