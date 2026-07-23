<?php

declare(strict_types=1);

use Zoosper\Admin\Form\AdminConfigLayeredFileLoader;

it('loads admin config through the layered runtime bridge', function (): void {
    $dir = sys_get_temp_dir() . '/zoosper-admin-layered-runtime-bridge-' . bin2hex(random_bytes(6));
    mkdir($dir, 0775, true);

    $moduleFile = $dir . '/module-admin_forms.php';
    $rootFile = $dir . '/root-admin_forms.php';

    file_put_contents($moduleFile, "<?php\nreturn ['admin_forms' => ['page' => ['sections' => ['seo' => ['enabled' => false, 'title' => 'Module SEO', 'fields' => ['meta_title', 'meta_description']]]]]];\n");
    file_put_contents($rootFile, "<?php\nreturn ['admin_forms' => ['page' => ['sections' => ['seo' => ['enabled' => true, 'title' => 'Root SEO']]]]];\n");

    $config = (new AdminConfigLayeredFileLoader())->load([
        'module:test-admin-forms' => $moduleFile,
        'root:test-admin-forms' => $rootFile,
    ]);

    expect($config['admin_forms']['page']['sections']['seo'])->toBe([
        'enabled' => true,
        'title' => 'Root SEO',
        'fields' => ['meta_title', 'meta_description'],
    ]);
});

it('keeps the admin config layered runtime bridge proof tool available', function (): void {
    $root = dirname(__DIR__, 5);

    expect($root . '/tools/prove-admin-config-layered-runtime-bridge.php')->toBeFile();
});
