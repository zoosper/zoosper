<?php

declare(strict_types=1);

namespace Zoosper\Auth\Tests\Feature;

use Zoosper\Core\Bootstrap\ApplicationFactory;
use Zoosper\AdminGrid\GridFeatureAcceptance;
use Zoosper\Auth\Admin\Grid\AdminUserGridIndex;
use Zoosper\Core\Testing\TestCase;
use Zoosper\Core\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;

/**
 * Phase 4.400 - DOM coverage for the Admin User Grid.
 *
 * Verifies that the Admin User Grid rendering satisfies the modern workspace
 * contract required by the Zoosper Admin Grid package.
 */
class AdminUserGridAcceptanceTest extends TestCase
{
    private function bootApp(): \Zoosper\Core\Http\Application
    {
        $basePath = dirname(__DIR__, 4);
        
        if (!function_exists('env')) {
            require_once $basePath . '/bootstrap/autoload.php';
        }

        // Force testing environment with SQLite in-memory
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['DB_DRIVER'] = 'sqlite';
        $_ENV['DB_DATABASE'] = ':memory:';
        $_ENV['APP_DEBUG'] = 'true';

        $app = ApplicationFactory::create($basePath);

        // ApplicationFactory installs early + main handlers; undo both to avoid leaking global state.
        restore_error_handler();
        restore_exception_handler();
        restore_error_handler();
        restore_exception_handler();

        return $app;
    }

    public function testAdminUserGridSatisfiesModernContract(): void
    {
        $basePath = dirname(__DIR__, 4);
        $app = $this->bootApp();
        $container = $app->services();

        // Run migrations for in-memory database
        $migrator = new Migrator(
            $container->get(\PDO::class),
            $basePath,
            $container->get(ModuleRegistry::class)
        );
        $migrator->migrate();

        /** @var AdminUserGridIndex $gridIndex */
        $gridIndex = $container->get(AdminUserGridIndex::class);
        
        // Render for a dummy user ID
        $html = $gridIndex->render(1, [], null);

        $acceptance = new GridFeatureAcceptance();
        $report = $acceptance->evaluate('admin.users', $html);

        $this->assertTrue(
            $report->isComplete(), 
            sprintf(
                "Admin User Grid '%s' is missing modern workspace markers.\nPassed: %s\nFailed: %s",
                $report->gridKey,
                implode(', ', $report->passed),
                implode(', ', $report->failed)
            )
        );
    }
}
