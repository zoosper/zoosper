<?php

declare(strict_types=1);

use Zoosper\Admin\Theme\AdminColourTheme;
use Zoosper\Admin\Theme\ModuleAdminColourThemeLoader;
use Zoosper\Core\Module\ModuleRegistry;

function removeAdminColourThemeFixture(string $directory): void
{
    if (!is_dir($directory)) {
        return;
    }

    foreach (scandir($directory) ?: [] as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $path = $directory . DIRECTORY_SEPARATOR . $item;
        is_dir($path) ? removeAdminColourThemeFixture($path) : unlink($path);
    }

    rmdir($directory);
}

/** @param array<string, array<string, mixed>> $themes */
function writeAdminColourThemeModule(string $base, string $module, int $sortOrder, array $themes): void
{
    $directory = $base . '/app/' . $module;
    mkdir($directory . '/config', 0775, true);
    file_put_contents($directory . '/module.php', "<?php\ndeclare(strict_types=1);\nreturn ['name' => '" . $module . "', 'enabled' => true, 'sort_order' => " . $sortOrder . "];\n");
    file_put_contents($directory . '/config/admin_colour_themes.php', "<?php\ndeclare(strict_types=1);\nreturn " . var_export(['themes' => $themes], true) . ";\n");
}

it('discovers enabled module palettes and sorts them deterministically', function (): void {
    $base = sys_get_temp_dir() . '/zoosper-admin-colour-themes-' . bin2hex(random_bytes(8));
    writeAdminColourThemeModule($base, 'module-a', 10, [
        'light' => ['name' => 'Light', 'mode' => 'light', 'sort_order' => 10],
        'dark' => ['name' => 'Dark', 'mode' => 'dark', 'sort_order' => 20],
    ]);
    writeAdminColourThemeModule($base, 'module-b', 20, [
        'violet' => ['name' => 'Violet', 'mode' => 'dark', 'sort_order' => 15],
    ]);

    try {
        $themes = (new ModuleAdminColourThemeLoader(new ModuleRegistry($base)))->all();

        expect(array_column($themes, 'code'))->toBe(['light', 'violet', 'dark'])
            ->and($themes[1])->toBeInstanceOf(AdminColourTheme::class)
            ->and($themes[1]->mode)->toBe('dark');
    } finally {
        removeAdminColourThemeFixture($base);
    }
});

it('rejects duplicate codes and malformed declarations', function (): void {
    $duplicate = sys_get_temp_dir() . '/zoosper-admin-colour-theme-duplicate-' . bin2hex(random_bytes(8));
    writeAdminColourThemeModule($duplicate, 'module-a', 10, [
        'light' => ['name' => 'Light', 'mode' => 'light'],
        'dark' => ['name' => 'Dark', 'mode' => 'dark'],
    ]);
    writeAdminColourThemeModule($duplicate, 'module-b', 20, [
        'dark' => ['name' => 'Other dark', 'mode' => 'dark'],
    ]);

    try {
        expect(fn (): array => (new ModuleAdminColourThemeLoader(new ModuleRegistry($duplicate)))->all())
            ->toThrow(RuntimeException::class, 'Duplicate Admin colour theme code: dark');
    } finally {
        removeAdminColourThemeFixture($duplicate);
    }

    expect(fn (): AdminColourTheme => new AdminColourTheme('Bad code', 'Bad', 'light'))
        ->toThrow(InvalidArgumentException::class);
    expect(fn (): AdminColourTheme => new AdminColourTheme('bad', 'Bad', 'sepia'))
        ->toThrow(InvalidArgumentException::class);
    expect(fn (): AdminColourTheme => AdminColourTheme::fromConfig('bad', [
        'name' => 'Bad',
        'mode' => 'light',
        'sort_order' => 'first',
    ]))->toThrow(InvalidArgumentException::class);
});

it('requires the compatibility light and dark palettes', function (): void {
    $base = sys_get_temp_dir() . '/zoosper-admin-colour-theme-required-' . bin2hex(random_bytes(8));
    writeAdminColourThemeModule($base, 'module-a', 10, [
        'light' => ['name' => 'Light', 'mode' => 'light'],
    ]);

    try {
        expect(fn (): array => (new ModuleAdminColourThemeLoader(new ModuleRegistry($base)))->all())
            ->toThrow(RuntimeException::class, 'Required Admin colour theme is not registered: dark');
    } finally {
        removeAdminColourThemeFixture($base);
    }
});










