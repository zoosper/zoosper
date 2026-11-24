<?php

declare(strict_types=1);

use Zoosper\AdminForm\AdminFormConfigAggregator;

it('discovers and merges form configuration without losing empty handles', function (): void {
    $root = sys_get_temp_dir() . '/zoosper_form_config_' . bin2hex(random_bytes(6));
    $directory = $root . '/app/example/config';
    mkdir($directory, 0777, true);
    $file = $directory . '/admin_forms.php';
    $module = [
        'forms' => ['example' => ['ProviderA', 'ProviderA', '', null], 'empty' => []],
        'processors' => ['example' => ['ProcessorA']],
    ];
    file_put_contents($file, '<?php return ' . var_export($module, true) . ';');

    try {
        $aggregator = new AdminFormConfigAggregator($root);
        expect($aggregator->aggregate([
            'forms' => ['example' => ['ProviderA', 'ProviderB'], 'invalid' => false],
            'processors' => ['example' => ['ProcessorB', 'ProcessorA']],
        ]))->toBe([
            'forms' => ['example' => ['ProviderA', 'ProviderB'], 'empty' => []],
            'processors' => ['example' => ['ProcessorA', 'ProcessorB']],
        ]);

        unlink($file);
        expect($aggregator->aggregate())->toBe(['forms' => [], 'processors' => []]);
    } finally {
        if (is_file($file)) {
            unlink($file);
        }
        rmdir($directory);
        rmdir($root . '/app/example');
        rmdir($root . '/app');
        rmdir($root);
    }
});