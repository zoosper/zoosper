<?php

declare(strict_types=1);

namespace Zoosper\Page\Tests\Feature;

use Zoosper\Core\Bootstrap\ApplicationFactory;
use Zoosper\AdminGrid\GridFeatureAcceptance;
use Zoosper\Page\Admin\PageAdminGridResponder;
use Zoosper\Core\Testing\TestCase;
use Zoosper\Database\Migrator;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Http\Request;
use Zoosper\Auth\Model\AdminUser;

/**
 * Phase 4.400 - DOM coverage for the Admin Page Grid.
 */
class AdminPageGridAcceptanceTest extends TestCase
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

        $app = ApplicationFactory::create($basePath);

        restore_error_handler();
        restore_exception_handler();
        restore_error_handler();
        restore_exception_handler();

        return $app;
    }

    public function testAdminPageGridSatisfiesModernContract(): void
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

        /** @var PageAdminGridResponder $responder */
        $responder = $container->get(PageAdminGridResponder::class);
        
        $request = new Request(
            method: 'GET',
            path: '/admin/pages',
            headers: [],
            body: '',
            query: [],
            host: 'localhost'
        );
        $user = new AdminUser(1, 'admin@example.com', 'Admin', 'hash', 'active', ['page.read']);
        
        $response = $responder->index($request, $user);
        $html = $response->body();
        file_put_contents('debug_page_grid.html', $html);

        $acceptance = new GridFeatureAcceptance();
        $report = $acceptance->evaluate('admin.pages', $html);

        $this->assertTrue(
            $report->isComplete(), 
            sprintf(
                "Admin Page Grid '%s' is missing modern workspace markers.\nPassed: %s\nFailed: %s",
                $report->gridKey,
                implode(', ', $report->passed),
                implode(', ', $report->failed)
            )
        );
    }
}










