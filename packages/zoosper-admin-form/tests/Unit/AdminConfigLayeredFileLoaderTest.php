<?php

declare(strict_types=1);

use Zoosper\AdminForm\AdminConfigLayeredFileLoader;

it('rejects invalid config paths', function (mixed $path): void {
    expect(fn () => (new AdminConfigLayeredFileLoader())->load([$path]))
        ->toThrow(RuntimeException::class, 'Admin config layered file loader expects non-empty file paths.');
})->with([null, false, 1, [[]], new stdClass(), '']);

it('loads named and numbered sources in order and skips missing files', function (): void {
    $directory = sys_get_temp_dir() . '/zoosper_admin_config_' . bin2hex(random_bytes(6));
    mkdir($directory);
    $first = $directory . '/first.php';
    $second = $directory . '/second.php';
    $invalid = $directory . '/invalid.php';
    file_put_contents($first, '<?php return ["value" => "first", "keep" => true];');
    file_put_contents($second, '<?php return ["value" => "second"];');
    file_put_contents($invalid, '<?php return false;');

    try {
        $loader = new AdminConfigLayeredFileLoader();
        expect($loader->load(['module' => $first, $directory . '/missing.php', 'root' => $second]))
            ->toBe(['value' => 'second', 'keep' => true]);
        expect(fn () => $loader->load([$invalid]))->toThrow(UnexpectedValueException::class);
    } finally {
        unlink($first);
        unlink($second);
        unlink($invalid);
        rmdir($directory);
    }
});

it('accepts zero-like and whitespace paths as non-empty strings', function (): void {
    expect((new AdminConfigLayeredFileLoader())->load(['0', ' ']))->toBe([]);
});