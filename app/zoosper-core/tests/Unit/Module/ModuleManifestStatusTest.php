<?php

declare(strict_types=1);

use Zoosper\Core\Module\ModuleManifestCompiler;
use Zoosper\Core\Module\ModuleManifestStatus;

function manifestStatusFixture(): string
{
    $root = sys_get_temp_dir() . '/zoosper-manifest-status-' . bin2hex(random_bytes(6));
    $module = $root . '/app/acme-example';
    mkdir($module, 0775, true);
    file_put_contents(
        $module . '/module.php',
        "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'acme-example', 'enabled' => true];\n",
    );

    return $root;
}

it('reports a missing optional manifest while counting live modules', function (): void {
    $root = manifestStatusFixture();
    $status = (new ModuleManifestStatus($root))->inspect();

    expect($status['status'])->toBe('missing')
        ->and($status['moduleCount'])->toBe(1)
        ->and($status['rejectionReason'])->toBeNull();

    exec('rm -rf ' . escapeshellarg($root));
});

it('reports a fresh compiled manifest', function (): void {
    $root = manifestStatusFixture();
    $cachePath = $root . '/var/cache/modules.php';
    (new ModuleManifestCompiler($root, $cachePath))->compile();

    $status = (new ModuleManifestStatus($root, $cachePath))->inspect();

    expect($status['status'])->toBe('fresh')
        ->and($status['moduleCount'])->toBe(1)
        ->and($status['rejectionReason'])->toBeNull();

    exec('rm -rf ' . escapeshellarg($root));
});

it('reports a rejected stale manifest and the Phase 8D reason', function (): void {
    $root = manifestStatusFixture();
    $cachePath = $root . '/var/cache/modules.php';
    (new ModuleManifestCompiler($root, $cachePath))->compile();

    mkdir($root . '/app/acme-fresh', 0775, true);
    file_put_contents(
        $root . '/app/acme-fresh/module.php',
        "<?php\ndeclare(strict_types=1);\nreturn ['name' => 'acme-fresh', 'enabled' => true];\n",
    );

    $status = (new ModuleManifestStatus($root, $cachePath))->inspect();

    expect($status['status'])->toBe('rejected')
        ->and($status['moduleCount'])->toBe(2)
        ->and($status['rejectionReason'])->toBe('first-party-modules-changed');

    exec('rm -rf ' . escapeshellarg($root));
});
