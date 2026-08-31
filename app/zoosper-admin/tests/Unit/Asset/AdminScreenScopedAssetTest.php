<?php

declare(strict_types=1);

use Zoosper\Admin\Asset\AdminAsset;
use Zoosper\Admin\Asset\AdminAssetRegistry;
use Zoosper\Core\Module\ModuleRegistry;

function removeScreenAssetFixture(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (scandir($dir) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? removeScreenAssetFixture($path) : unlink($path);
    }

    rmdir($dir);
}

it('filters module assets by generic Admin screen before physical-path deduplication', function (): void {
    $base = sys_get_temp_dir() . '/zoosper-screen-assets-' . bin2hex(random_bytes(8));
    $module = $base . '/app/module-a';
    mkdir($module . '/config', 0775, true);
    file_put_contents($module . '/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'module-a', 'enabled' => true];\n");
    file_put_contents($module . '/config/admin_assets.php', <<<'PHP'
<?php

declare(strict_types=1);

return ['assets' => [
    'pages-early' => ['type' => 'script', 'path' => '/assets/shared.js?v=pages', 'sort_order' => 10, 'screens' => ['pages']],
    'global-late' => ['type' => 'script', 'path' => '/assets/shared.js?v=global', 'sort_order' => 20],
    'settings-only' => ['type' => 'style', 'path' => '/assets/settings.css', 'sort_order' => 30, 'screens' => ['settings']],
]];
PHP);

    try {
        $registry = new AdminAssetRegistry(new ModuleRegistry($base));

        expect(array_column($registry->all('pages'), 'handle'))->toBe(['pages-early'])
            ->and(array_column($registry->all('settings'), 'handle'))->toBe(['global-late', 'settings-only'])
            ->and(array_column($registry->all('access-tokens'), 'handle'))->toBe(['global-late'])
            ->and(array_column($registry->all(), 'handle'))->toBe(['pages-early', 'settings-only']);
    } finally {
        removeScreenAssetFixture($base);
    }
});

it('keeps absent screens global and rejects malformed applicability declarations', function (): void {
    $global = AdminAsset::fromConfig('global', ['type' => 'style', 'path' => '/global.css']);
    $pages = AdminAsset::fromConfig('pages', ['type' => 'script', 'path' => '/pages.js', 'screens' => ['pages', 'pages']]);

    expect($global->appliesTo('settings'))->toBeTrue()
        ->and($pages->screens)->toBe(['pages'])
        ->and($pages->appliesTo('pages'))->toBeTrue()
        ->and($pages->appliesTo('settings'))->toBeFalse()
        ->and($pages->appliesTo(null))->toBeTrue();

    expect(fn (): AdminAsset => AdminAsset::fromConfig('bad', [
        'path' => '/bad.js',
        'screens' => 'pages',
    ]))->toThrow(\InvalidArgumentException::class, 'Admin asset screens must be an array.');
});

it('keeps Settings and EditorJS assets off unrelated real Admin screens', function (): void {
    $root = dirname(__DIR__, 5);
    $registry = new AdminAssetRegistry(new ModuleRegistry($root));

    $accessTokens = array_column($registry->all('access-tokens'), 'handle');
    $settings = array_column($registry->all('settings'), 'handle');
    $pages = array_column($registry->all('pages'), 'handle');
    $editorHandles = [
        'zoosper-admin-editor-style',
        'zoosper-admin-editorjs-bundle',
        'zoosper-admin-editor-script',
    ];
    $settingsHandles = [
        'zoosper-settings-workspace-style',
        'zoosper-settings-workspace-script',
    ];

    expect(array_intersect($accessTokens, [...$editorHandles, ...$settingsHandles]))->toBe([])
        ->and(array_intersect($settings, $settingsHandles))->toHaveCount(2)
        ->and(array_intersect($settings, $editorHandles))->toBe([])
        ->and(array_intersect($pages, $editorHandles))->toHaveCount(3)
        ->and(array_intersect($pages, $settingsHandles))->toBe([]);
});

it('passes the active screen through both Admin layout asset rendering paths', function (): void {
    $root = dirname(__DIR__, 5);
    $layout = (string) file_get_contents($root . '/app/zoosper-admin/src/Layout/AdminLayout.php');
    $controller = (string) file_get_contents($root . '/app/zoosper-page/src/Admin/Controller/PageAdminController.php');
    $manifest = require $root . '/app/zoosper-editor/config/admin_assets.php';

    expect($layout)->toContain('$this->assetViewData?->data($active)')
        ->toContain('$this->assetRenderer?->stylesHtml($active)')
        ->toContain('$this->assetRenderer?->scriptsHtml($active)')
        ->and($controller)->toContain("->render(\$title, \$content, \$this->guard->user(), 'pages')")
        ->and($manifest['assets']['zoosper-admin-editor-style']['screens'])->toBe(['pages'])
        ->and($manifest['assets']['zoosper-admin-editorjs-bundle']['screens'])->toBe(['pages'])
        ->and($manifest['assets']['zoosper-admin-editor-script']['screens'])->toBe(['pages']);
});
