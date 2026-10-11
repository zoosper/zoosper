<?php

declare(strict_types=1);

use Zoosper\Admin\Asset\AdminAssetRegistry;
use Zoosper\Core\Module\ModuleRegistry;

function removeInvalidAssetFixture(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    foreach (scandir($dir) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $dir . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? removeInvalidAssetFixture($path) : unlink($path);
    }

    rmdir($dir);
}

it('rejects malformed flat and wrapped declarations at the manifest boundary', function (string $manifest): void {
    $base = sys_get_temp_dir() . '/zoosper-invalid-admin-assets-' . bin2hex(random_bytes(8));
    $module = $base . '/app/invalid-assets';
    mkdir($module . '/config', 0775, true);
    file_put_contents($module . '/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'invalid-assets', 'enabled' => true];\n");
    file_put_contents($module . '/config/admin_assets.php', $manifest);

    try {
        expect(fn (): array => (new AdminAssetRegistry(new ModuleRegistry($base)))->all())
            ->toThrow(RuntimeException::class, 'Invalid admin asset declaration in:');
    } finally {
        removeInvalidAssetFixture($base);
    }
})->with([
    'numeric flat handle' => ["<?php\ndeclare(strict_types=1);\nreturn [0 => ['path' => '/bad.js']];\n"],
    'empty wrapped handle' => ["<?php\ndeclare(strict_types=1);\nreturn ['assets' => ['' => ['path' => '/bad.js']]];\n"],
    'non-array declaration' => ["<?php\ndeclare(strict_types=1);\nreturn ['assets' => ['bad' => '/bad.js']];\n"],
]);
