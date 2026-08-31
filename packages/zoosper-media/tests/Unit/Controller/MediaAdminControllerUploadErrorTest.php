<?php

declare(strict_types=1);

use Zoosper\AdminForm\AdminFormDefinition;
use Zoosper\AdminForm\AdminFormField;
use Zoosper\AdminForm\AdminFormRegistry;
use Zoosper\AdminForm\AdminFormRenderer;
use Zoosper\Auth\Model\AdminUser;
use Zoosper\Auth\Repository\AdminUserRepository;
use Zoosper\Auth\Service\CsrfTokenManager;
use Zoosper\Auth\Service\PasswordHasher;
use Zoosper\Auth\Service\SessionGuard;
use Zoosper\Auth\UI\AdminViewRendererInterface;
use Zoosper\Core\Database\Migrator;
use Zoosper\Core\Http\Request;
use Zoosper\Core\Module\ModuleRegistry;
use Zoosper\Media\Controller\MediaAdminController;
use Zoosper\Media\Repository\MediaAssetRepository;
use Zoosper\Media\Service\MediaStorage;
use Zoosper\Media\Service\MediaUploadService;
use Zoosper\Media\Service\MediaUploadValidator;

/**
 * Bug-fix regression test — proves MediaAdminController::upload() now shows
 * the real failure reason instead of silently redirecting to /admin/media
 * on a rejected upload.
 *
 * SessionGuard, MediaUploadService, MediaAssetRepository, MediaStorage, and
 * MediaUploadValidator are all `final` classes, so this test uses REAL
 * instances throughout (same proven approach as
 * app/zoosper-auth/tests/Unit/Console/AdminCreateCommandTest.php: a real
 * SessionGuard + real AdminUserRepository backed by a fresh in-memory
 * SQLite database via the real Migrator), rather than attempting to
 * subclass/mock final classes, which PHP does not allow.
 *
 * FIX: SessionGuard::login() calls session_regenerate_id(true), which
 * requires an ACTIVE session to regenerate. Pest's CLI test runner does not
 * start one automatically, so each test that calls login() must start a
 * real session first (session_start()) and clean it up afterward
 * (session_write_close() + session state reset) so tests don't leak session
 * state into each other.
 *
 * The validation failure itself is genuine, not simulated: a real temp file
 * with a `.txt` extension is passed through the real MediaUploadValidator,
 * which rejects it because `.txt` is not in its allowed-extensions list
 * (only jpg/jpeg/png/gif/webp) — see MediaUploadValidator::ALLOWED.
 *
 * AdminViewRendererInterface IS safe to fake here (it's a genuine
 * interface, not a final class) — the anonymous class below captures what
 * was rendered so the test can assert on it.
 *
 * File placement: packages/zoosper-media/tests/Unit/Controller/MediaAdminControllerUploadErrorTest.php
 * — 5 levels up to repo root, matching other per-module tests.
 */
function mediaUploadErrorTestDatabase(): PDO
{
    $basePath = dirname(__DIR__, 5);
    $pdo = new PDO('sqlite::memory:');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    (new Migrator($pdo, $basePath, new ModuleRegistry($basePath)))->migrate();

    return $pdo;
}

function mediaUploadErrorTestUser(PDO $pdo): AdminUser
{
    $users = new AdminUserRepository($pdo);
    $email = 'media-upload-test-' . bin2hex(random_bytes(4)) . '@example.test';
    $users->create($email, 'Media Test Admin', (new PasswordHasher())->hash('ChangeMe123!'), 'super_admin');

    return $users->findByEmail($email);
}

/**
 * Start a real PHP session so SessionGuard::login()'s
 * session_regenerate_id(true) call has an active session to regenerate,
 * matching how this code actually runs in production (behind a real HTTP
 * request, where the session is already started by that point).
 */
function mediaUploadErrorTestStartSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
}

/**
 * Clean up session state between tests so nothing leaks across test cases.
 */
function mediaUploadErrorTestEndSession(): void
{
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}

it('shows the real validation error message instead of silently redirecting on a rejected upload', function (): void {
    mediaUploadErrorTestStartSession();

    $pdo = mediaUploadErrorTestDatabase();
    $user = mediaUploadErrorTestUser($pdo);

    $guard = new SessionGuard(new AdminUserRepository($pdo));
    $guard->login($user);

    $captured = [];
    $views = new class ($captured) implements AdminViewRendererInterface {
        public function __construct(private array &$outer)
        {
        }

        public function render(string $title, string $template, array $data, ?AdminUser $user, string $active = 'dashboard'): string
        {
            $this->outer[] = ['title' => $title, 'template' => $template, 'data' => $data];

            return '<html>' . $title . '</html>';
        }
    };

    $formRegistry = new AdminFormRegistry();
    $formRegistry->register(new AdminFormDefinition(
        'admin.media.upload.form',
        [new AdminFormField('media_file', 'file', 'Image file', 10)]
    ));

    $controller = new MediaAdminController(
        guard: $guard,
        csrf: new CsrfTokenManager(),
        views: $views,
        assets: $assets = new MediaAssetRepository($pdo),
        uploads: new MediaUploadService(
            assets: $assets,
            validator: new MediaUploadValidator(),
            storage: new MediaStorage(sys_get_temp_dir() . '/zoosper-media-upload-test-' . bin2hex(random_bytes(4))),
            basePath: sys_get_temp_dir(),
        ),
        formRegistry: $formRegistry,
        formRenderer: new AdminFormRenderer(),
    );

    // Genuinely invalid: a real temp file with a disallowed .txt extension.
    $tmpFile = tempnam(sys_get_temp_dir(), 'zoosper-upload-test-');
    file_put_contents($tmpFile, 'not an image');
    $request = new Request(method: 'POST', path: '/admin/media/upload', files: [
        'media_file' => [
            'name' => 'not-an-image.txt',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmpFile),
        ],
    ]);
    $response = $controller->upload($request);

    ob_start();
    $response->send();
    ob_end_clean();

    // The critical assertions: this is no longer a silent redirect.
    expect(http_response_code())->toBe(422);
    expect($captured)->toHaveCount(1);
    expect($captured[0]['template'])->toBe('zoosper-admin::admin/generic/form');
    expect($captured[0]['data']['formHtml'])->toContain('media_file');
    expect($captured[0]['data']['formHtml'])->toContain('admin-alert--danger');
    expect($captured[0]['data']['formHtml'])->toContain('Unsupported media file extension');

    unlink($tmpFile);
    mediaUploadErrorTestEndSession();
});

it('still redirects to the media library on a successful upload', function (): void {
    mediaUploadErrorTestStartSession();

    $pdo = mediaUploadErrorTestDatabase();
    $user = mediaUploadErrorTestUser($pdo);

    $guard = new SessionGuard(new AdminUserRepository($pdo));
    $guard->login($user);

    $captured = [];
    $views = new class ($captured) implements AdminViewRendererInterface {
        public function __construct(private array &$outer)
        {
        }

        public function render(string $title, string $template, array $data, ?AdminUser $user, string $active = 'dashboard'): string
        {
            $this->outer[] = ['title' => $title, 'template' => $template, 'data' => $data];

            return '<html>' . $title . '</html>';
        }
    };

    $storageDir = sys_get_temp_dir() . '/zoosper-media-upload-test-' . bin2hex(random_bytes(4));
    $formRegistry = new AdminFormRegistry();
    $formRegistry->register(new AdminFormDefinition(
        'admin.media.upload.form',
        [new AdminFormField('media_file', 'file', 'Image file', 10)]
    ));

    $controller = new MediaAdminController(
        guard: $guard,
        csrf: new CsrfTokenManager(),
        views: $views,
        assets: $assets = new MediaAssetRepository($pdo),
        uploads: new MediaUploadService(
            assets: $assets,
            validator: new MediaUploadValidator(),
            storage: new MediaStorage($storageDir),
            basePath: $storageDir,
        ),
        formRegistry: $formRegistry,
        formRenderer: new AdminFormRenderer(),
    );

    // A genuine JPEG submitted under the upload form's media_file field.
    $tmpFile = tempnam(sys_get_temp_dir(), 'zoosper-upload-test-');
    $image = imagecreatetruecolor(2, 2);
    imagejpeg($image, $tmpFile, 90);
    unset($image);
    $request = new Request(method: 'POST', path: '/admin/media/upload', files: [
        'media_file' => [
            'name' => 'valid.jpg',
            'tmp_name' => $tmpFile,
            'error' => UPLOAD_ERR_OK,
            'size' => filesize($tmpFile),
        ],
    ]);
    $response = $controller->upload($request);

    ob_start();
    $response->send();
    ob_end_clean();

    expect(http_response_code())->toBe(302);
    expect($captured)->toBe([]);

    unlink($tmpFile);
    mediaUploadErrorTestEndSession();
});
