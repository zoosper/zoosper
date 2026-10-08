<?php

declare(strict_types=1);

use Zoosper\Core\Release\ReleaseCheck;

/** Build isolated real-discovery fixtures; never modify the application or artifact. */
it('resolves module-owned release files and fails closed when missing', function (string $layout): void {
    $root = sys_get_temp_dir() . '/zoosper-release-layout-' . bin2hex(random_bytes(8));
    $files = [
        'settings' => ['resources/assets/css/settings-workspace.css', 'resources/assets/js/settings-workspace.js'],
        'page' => ['src/Console/StarterSiteInstallCommand.php'],
        'session' => ['config/settings/session.php'],
    ];
    try {
        foreach ($files as $module => $relativeFiles) {
            $path = $root . '/' . ($layout === 'vendor' ? 'vendor/zoosper/' . $module : $layout . '/zoosper-' . $module);
            mkdir($path, 0700, true);
            file_put_contents($path . '/module.php', '<?php return [];');
            file_put_contents($path . '/composer.json', json_encode(['name' => 'zoosper/' . $module, 'type' => 'zoosper-module'], JSON_THROW_ON_ERROR));
            foreach ($relativeFiles as $relative) {
                mkdir(dirname($path . '/' . $relative), 0700, true);
                file_put_contents($path . '/' . $relative, 'fixture');
            }
        }
        $results = (new ReleaseCheck($root))->moduleFileResults();
        expect($results)->toHaveCount(4);
        foreach ($results as $result) {
            expect($result->passed)->toBeTrue()->and($result->message)->toStartWith($root . '/');
        }
        $settings = $root . '/' . ($layout === 'vendor' ? 'vendor/zoosper/settings' : $layout . '/zoosper-settings');
        unlink($settings . '/resources/assets/css/settings-workspace.css');
        $results = (new ReleaseCheck($root))->moduleFileResults();
        expect($results[0]->passed)->toBeFalse()->and($results[1]->passed)->toBeTrue();
        unlink($settings . '/module.php');
        $results = (new ReleaseCheck($root))->moduleFileResults();
        expect($results[0]->passed)->toBeFalse()->and($results[1]->passed)->toBeFalse();
    } finally {
        if (is_dir($root)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $entry) {
                $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
            }
            rmdir($root);
        }
    }
})->with(['app', 'modules', 'vendor']);

/** Evaluate relocated settings in a clean child process, without ambient env helpers. */
it('roots default and relative session storage at the owning project', function (string $layout): void {
    $root = sys_get_temp_dir() . '/zoosper-session-layout-' . bin2hex(random_bytes(8));
    $path = $root . '/' . ($layout === 'vendor' ? 'vendor/zoosper/session' : 'app/zoosper-session') . '/config/settings/session.php';
    try {
        mkdir(dirname($path), 0700, true);
        file_put_contents($root . '/composer.json', '{"name":"zoosper/zoosper"}');
        copy(dirname(__DIR__, 5) . '/app/zoosper-session/config/settings/session.php', $path);
        foreach (['var/sessions', 'custom/sessions', '/tmp/absolute-session-fixture', 'scheme://sessions'] as $configured) {
            $code = 'function env($key,$default=null){return $key === "SESSION_STORAGE_PATH" ? $GLOBALS["configured"] : $default;} $GLOBALS["configured"]=$argv[2]; echo json_encode(require $argv[1]);';
            $process = proc_open([PHP_BINARY, '-r', $code, $path, $configured], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            if (!is_resource($process)) { throw new RuntimeException('Cannot start settings probe.'); }
            $output = stream_get_contents($pipes[1]);
            $error = stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            expect(proc_close($process))->toBe(0, $error);
            $settings = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
            $expected = str_starts_with($configured, '/') || str_contains($configured, '://') ? $configured : $root . '/' . $configured;
            expect($settings['path'])->toBe($expected);
        }
    } finally {
        if (is_dir($root)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($iterator as $entry) {
                $entry->isDir() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
            }
            rmdir($root);
        }
    }
})->with(['app', 'vendor']);
