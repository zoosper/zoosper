<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Feature;

use Zoosper\Core\Bootstrap\ApplicationFactory;
use Zoosper\Page\Admin\Controller\PageAdminController;
use Zoosper\Core\Testing\TestCase;
use Zoosper\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Http\Request;

class AdminPageFormAcceptanceTest extends TestCase
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

    public function testAdminPageCreateFormRendersUnifiedLayout(): void
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

        /** @var PageAdminController $controller */
        $controller = $container->get(PageAdminController::class);
        
        $request = Request::fromGlobals();
        $response = $controller->createForm($request);
        
        $html = $response->body();

        // Verify unified form markers
        $this->assertStringContainsString('class="admin-page-workspace"', $html);
        $this->assertStringContainsString('class="admin-form"', $html);
        $this->assertStringContainsString('class="card admin-form-section"', $html);
        $this->assertStringContainsString('Page details', $html);
        $this->assertStringContainsString('Content', $html);
        $this->assertStringContainsString('Search engine optimisation', $html);
        $this->assertStringContainsString('Publishing', $html);
        
        // Verify CSRF token
        $this->assertStringContainsString('name="_csrf_token"', $html);
    }

    public function testAdminPageEditFormRendersRevisionsAndLifecycle(): void
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

        $pdo = $container->get(\PDO::class);
        $pdo->exec("INSERT INTO admin_users (id, email, name, password_hash, status, created_at, updated_at) VALUES (1, 'admin@example.com', 'Admin', 'hash', 'active', '2026-01-01', '2026-01-01')");
        $pdo->exec("INSERT INTO sites (id, name, code, status, created_at, updated_at) VALUES (1, 'Default Site', 'default', 'active', '2026-01-01', '2026-01-01')");
        $pdo->exec("INSERT INTO pages (id, site_id, title, slug, content, status, created_at, updated_at) VALUES (1, 1, 'Test Page', 'test-page', 'Content', 'published', '2026-01-01', '2026-01-01')");
        
        $_SESSION['admin_user_id'] = 1;
        $_SESSION['admin_password_hash_fingerprint'] = hash('sha256', 'hash');
        $_SESSION['admin_last_activity_at'] = time();

        $_SERVER['REQUEST_URI'] = '/admin/pages/edit?id=1';
        $_GET['id'] = '1';

        /** @var PageAdminController $controller */
        $controller = $container->get(PageAdminController::class);
        
        $request = Request::fromGlobals();
        
        $response = $controller->editForm($request);
        
        $html = $response->body();

        $this->assertStringContainsString('Edit page', $html);
        $this->assertStringContainsString('value="Test Page"', $html);
        
        // Verify history and lifecycle sections
        $this->assertStringContainsString('class="admin-page-history"', $html);
        $this->assertStringContainsString('class="admin-page-lifecycle"', $html);
    }
}










