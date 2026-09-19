<?php

declare(strict_types=1);

namespace Zoosper\Media\Tests\Feature;

use PDO;
use Zoosper\Core\Bootstrap\ApplicationFactory;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Core\Testing\TestCase;
use Zoosper\Database\Migrator;
use Zoosper\Media\Controller\MediaEditorJsLibraryController;
use Zoosper\Page\Admin\Controller\PageAdminController;

final class MediaPickerHttpAcceptanceTest extends TestCase
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

    public function testPickerControllerReturnsOnlySafeActiveImageRepresentation(): void
    {
        $basePath = dirname(__DIR__, 4);
        $app = $this->bootApp();
        $services = $app->services();
        $pdo = $services->get(PDO::class);
        (new Migrator($pdo, $basePath, $services->get(ModuleRegistry::class)))->migrate();

        $columns = [];
        foreach ($pdo->query('PRAGMA table_info(media_assets)')->fetchAll(PDO::FETCH_ASSOC) as $column) {
            $columns[(string) $column['name']] = $column;
        }
        $row = [
            'uuid' => '11111111-1111-4111-8111-111111111111',
            'filename' => 'hero.jpg',
            'original_filename' => 'Hero.jpg',
            'mime_type' => 'image/jpeg',
            'extension' => 'jpg',
            'size_bytes' => 123,
            'storage_path' => '/private/media/hero.jpg',
            'public_path' => '/media/hero.jpg',
            'status' => 'active',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-01-01 00:00:00',
        ];
        $insert = [];
        foreach ($row as $name => $value) {
            if (isset($columns[$name])) {
                $insert[$name] = $value;
            }
        }
        $names = array_keys($insert);
        $statement = $pdo->prepare('INSERT INTO media_assets (' . implode(', ', $names) . ') VALUES (:' . implode(', :', $names) . ')');
        $statement->execute($insert);

        /** @var MediaEditorJsLibraryController $controller */
        $controller = $services->get(MediaEditorJsLibraryController::class);
        $response = $controller->index(new Request('GET', '/admin/media/editorjs/library', query: ['q' => 'Hero', 'page' => '1', 'page_size' => '20']));
        self::assertSame(200, $response->statusCode());
        self::assertStringContainsString('application/json', strtolower((string) ($response->headers()['Content-Type'] ?? '')));
        $payload = json_decode($response->body(), true, 512, JSON_THROW_ON_ERROR);
        self::assertSame('/media/hero.jpg', $payload['items'][0]['url'] ?? null);
        self::assertSame('Hero.jpg', $payload['items'][0]['original_filename'] ?? null);
        self::assertArrayNotHasKey('storage_path', $payload['items'][0] ?? []);
        self::assertArrayNotHasKey('created_by', $payload['items'][0] ?? []);
        self::assertArrayNotHasKey('uuid', $payload['items'][0] ?? []);
        self::assertSame(1, $payload['pagination']['total'] ?? null);
    }

    public function testRenderedPageFormPublishesPickerAssetsOnceInDependencyOrder(): void
    {
        $basePath = dirname(__DIR__, 4);
        $app = $this->bootApp();
        $services = $app->services();
        $pdo = $services->get(PDO::class);
        (new Migrator($pdo, $basePath, $services->get(ModuleRegistry::class)))->migrate();
        $pdo->exec("INSERT INTO admin_users (id, email, name, password_hash, status, created_at, updated_at) VALUES (1, 'admin@example.test', 'Admin', 'hash', 'active', '2026-01-01', '2026-01-01')");
        $_SESSION['admin_user_id'] = 1;
        $_SESSION['admin_password_hash_fingerprint'] = hash('sha256', 'hash');
        $_SESSION['admin_last_activity_at'] = time();

        /** @var PageAdminController $controller */
        $controller = $services->get(PageAdminController::class);
        $html = $controller->createForm(new Request('GET', '/admin/pages/create'))->body();
        foreach (['zoosper-editor-bridge.js', 'editor-media-picker.js', 'editor-media-picker.css', 'data-zoosper-image-tool'] as $marker) {
            self::assertSame(1, substr_count($html, $marker), $marker . ' must be rendered exactly once.');
        }
        self::assertLessThan(strpos($html, 'editor-media-picker.js'), strpos($html, 'zoosper-editor-bridge.js'));
        self::assertStringNotContainsString('/private/media/', $html);
    }
}
