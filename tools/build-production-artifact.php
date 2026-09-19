<?php

declare(strict_types=1);

const ROOT_FILES = [
    '.env.example', 'LICENSE', 'README.md', 'SECURITY.md',
    'composer.json', 'composer.lock',
];
const ROOT_DIRECTORIES = [
    'app', 'bin', 'bootstrap', 'config', 'database', 'modules',
    'packages', 'public', 'storage', 'themes',
];
const PROHIBITED_SEGMENTS = [
    '.git', '.github', '.githooks', '.claude', '.idea', '.vscode',
    'node_modules', 'tests', 'test', 'fixtures', 'coverage',
    'docs', 'docs-site', 'tools',
];
const PROHIBITED_FILES = [
    'AGENTS.md', 'CLAUDE.md', 'ROADMAP.md', 'CHANGELOG.md',
    'package.json', 'package-lock.json', 'phpunit.xml',
    'psalm.xml', 'psalm-baseline.xml', 'phpstan.neon',
    'vite.admin-editor.config.js', '.gitignore', '.gitattributes',
];

$options = getopt('', ['output:', 'allow-dirty', 'keep-workspace']);
$outputDirectory = $options['output'] ?? null;
if (!is_string($outputDirectory) || trim($outputDirectory) === '') {
    fwrite(STDERR, "Usage: php8.5 tools/build-production-artifact.php --output=/absolute/path [--allow-dirty] [--keep-workspace]\n");
    exit(2);
}
if (!str_starts_with($outputDirectory, '/')) {
    throw new RuntimeException('Artifact output directory must be absolute.');
}

$root = dirname(__DIR__);
if (trim((string) shell_exec('id -un')) !== 'vagrant') {
    throw new RuntimeException('Production artifact creation must run as vagrant.');
}
if (!is_file($root . '/composer.lock') || !is_file($root . '/bin/zoosper')) {
    throw new RuntimeException('Repository root could not be verified.');
}
$dirty = trim(run(['git', '-C', $root, 'status', '--porcelain=v1'], null, true));
if ($dirty !== '' && !array_key_exists('allow-dirty', $options)) {
    throw new RuntimeException('The production artifact requires a clean worktree. Use --allow-dirty only for pre-commit verification.');
}
if (trim(run(['git', '-C', $root, 'diff', '--cached', '--name-only'], null, true)) !== '') {
    throw new RuntimeException('The real Git index must remain empty during artifact creation.');
}

$commit = trim(run(['git', '-C', $root, 'rev-parse', 'HEAD'], null, true));
$commitTime = (int) trim(run(['git', '-C', $root, 'show', '-s', '--format=%ct', 'HEAD'], null, true));
$versionOutput = trim(run(['php8.5', $root . '/bin/zoosper', 'version'], $root, true));
if (preg_match('/\b(\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?)\b/', $versionOutput, $matches) !== 1) {
    throw new RuntimeException('Unable to resolve the canonical semantic version from bin/zoosper version output.');
}
$version = $matches[1];
$workspace = sys_get_temp_dir() . '/zoosper-artifact-' . bin2hex(random_bytes(8));
$source = $workspace . '/source';
$release = $workspace . '/release';
$tempIndex = $workspace . '/index';

try {
    mkdir($source, 0700, true);
    mkdir($release, 0700, true);
    if (!is_dir($outputDirectory) && !mkdir($outputDirectory, 0700, true) && !is_dir($outputDirectory)) {
        throw new RuntimeException('Unable to create artifact output directory.');
    }

    copy($root . '/.git/index', $tempIndex);
    $environment = ['GIT_INDEX_FILE' => $tempIndex];
    run(['git', '-C', $root, 'add', '-A'], null, false, $environment);
    $tree = trim(run(['git', '-C', $root, 'write-tree'], null, true, $environment));
    $tracked = preg_split('/\R/', trim(run(['git', '-C', $root, 'ls-tree', '-r', '--name-only', $tree], null, true))) ?: [];

    foreach ($tracked as $path) {
        if ($path === '' || !allowed($path)) {
            continue;
        }
        $destination = $source . '/' . $path;
        if (!is_dir(dirname($destination)) && !mkdir(dirname($destination), 0700, true) && !is_dir(dirname($destination))) {
            throw new RuntimeException('Unable to create artifact source directory.');
        }
        $contents = run(['git', '-C', $root, 'show', $tree . ':' . $path], null, true);
        if (file_put_contents($destination, $contents) !== strlen($contents)) {
            throw new RuntimeException('Unable to write artifact source: ' . $path);
        }
        $mode = trim(run(['git', '-C', $root, 'ls-tree', $tree, '--', $path], null, true));
        chmod($destination, str_starts_with($mode, '100755') ? 0755 : 0644);
    }

    run([
        'php8.5', '/usr/local/bin/composer', 'install', '--working-dir=' . $source,
        '--no-dev', '--prefer-dist', '--classmap-authoritative', '--no-interaction', '--no-progress', '--no-ansi',
    ], $source);
    materialiseVendorSymlinks($source . '/vendor');
    pruneProductionTree($source . '/vendor');
    removeTree($source . '/app');
    removeTree($source . '/packages');
    run(['php8.5', '/usr/local/bin/composer', 'dump-autoload', '--working-dir=' . $source, '--no-dev', '--classmap-authoritative', '--no-interaction', '--no-ansi'], $source);

    copyTree($source, $release);
    foreach (['cache', 'log', 'sessions'] as $directory) {
        $path = $release . '/var/' . $directory;
        if (!is_dir($path) && !mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Unable to create runtime directory: ' . $directory);
        }
        file_put_contents($path . '/.gitkeep', '');
    }

    run(['php8.5', 'bin/zoosper', 'compile'], $release);
    run(['php8.5', 'bin/zoosper', 'module:manifest:check'], $release);
    assertProductionTree($release);

    $manifest = [
        'schema' => 1,
        'product' => 'zoosper',
        'version' => $version,
        'commit' => $commit,
        'source_date_epoch' => $commitTime,
        'php' => PHP_VERSION,
        'module_count' => count(require $release . '/var/cache/modules.php'),
        'file_count' => countFiles($release),
    ];
    file_put_contents($release . '/RELEASE-MANIFEST.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");

    $base = 'zoosper-' . $version . '-' . substr($commit, 0, 12);
    $tar = $outputDirectory . '/' . $base . '.tar';
    $archive = $tar . '.gz';
    run(['tar', '--sort=name', '--mtime=@' . $commitTime, '--owner=0', '--group=0', '--numeric-owner', '--format=posix', '-cf', $tar, '-C', $release, '.']);
    run(['gzip', '-n', '-9', $tar]);
    $checksum = hash_file('sha256', $archive);
    if ($checksum === false) {
        throw new RuntimeException('Unable to calculate artifact checksum.');
    }
    file_put_contents($archive . '.sha256', $checksum . '  ' . basename($archive) . "\n");

    $verify = $workspace . '/verify';
    mkdir($verify, 0700, true);
    run(['tar', '-xzf', $archive, '-C', $verify]);
    assertProductionTree($verify);
    run(['sha256sum', '-c', $archive . '.sha256'], $outputDirectory);
    run(['php8.5', 'bin/zoosper', 'module:manifest:check'], $verify);

    echo "Artifact: {$archive}\n";
    echo "Checksum: {$archive}.sha256\n";
    echo "Commit: {$commit}\n";
    echo "Files: {$manifest['file_count']}\n";
    echo "Modules: {$manifest['module_count']}\n";
} finally {
    if (!array_key_exists('keep-workspace', $options)) {
        removeTree($workspace);
    } else {
        fwrite(STDERR, "Workspace retained: {$workspace}\n");
    }
}

function allowed(string $path): bool
{
    if (in_array($path, ROOT_FILES, true)) {
        return true;
    }
    $first = explode('/', $path, 2)[0];
    if (!in_array($first, ROOT_DIRECTORIES, true)) {
        return false;
    }
    $segments = explode('/', $path);
    foreach ($segments as $segment) {
        if (in_array(strtolower($segment), PROHIBITED_SEGMENTS, true)) {
            return false;
        }
    }
    $basename = basename($path);
    if (in_array($basename, PROHIBITED_FILES, true) || preg_match('/(?:Test\.php|\.test\.js|\.spec\.js)$/', $basename) === 1) {
        return false;
    }
    return true;
}

function assertProductionTree(string $root): void
{
    foreach (['app', 'packages'] as $sourceHome) {
        if (file_exists($root . '/' . $sourceHome)) {
            throw new RuntimeException('Materialised production artifact must not retain first-party source home: ' . $sourceHome);
        }
    }
    foreach (PROHIBITED_FILES as $file) {
        if (file_exists($root . '/' . $file)) {
            throw new RuntimeException('Prohibited production artifact file: ' . $file);
        }
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $file) {
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        foreach (explode('/', $relative) as $segment) {
            if (in_array(strtolower($segment), PROHIBITED_SEGMENTS, true)) {
                throw new RuntimeException('Prohibited production artifact path: ' . $relative);
            }
        }
        if ($file->isLink()) {
            throw new RuntimeException('Production artifact must not contain symbolic links: ' . $relative);
        }
    }
    foreach (['composer.lock', 'vendor/autoload.php', 'vendor/zoosper/core/module.php', 'vendor/zoosper/database/module.php', 'public/index.php', 'bin/zoosper', 'var/cache/modules.php'] as $required) {
        if (!is_file($root . '/' . $required)) {
            throw new RuntimeException('Required production artifact file is missing: ' . $required);
        }
    }
}

function pruneProductionTree(string $root): void
{
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST,
    );
    foreach ($iterator as $file) {
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
        $segments = array_map('strtolower', explode('/', $relative));
        $basename = basename($relative);
        $prohibited = array_intersect($segments, PROHIBITED_SEGMENTS) !== []
            || in_array($basename, PROHIBITED_FILES, true)
            || preg_match('/(?:Test\.php|\.test\.js|\.spec\.js)$/', $basename) === 1;
        if (!$prohibited) {
            continue;
        }
        if ($file->isDir() && !$file->isLink()) {
            removeTree($file->getPathname());
        } else {
            unlink($file->getPathname());
        }
    }
}

function materialiseVendorSymlinks(string $vendor): void
{
    if (!is_dir($vendor)) {
        throw new RuntimeException('Production vendor directory is missing.');
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($vendor, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    $links = [];
    foreach ($iterator as $file) {
        if ($file->isLink()) {
            $links[] = $file->getPathname();
        }
    }
    usort($links, static fn(string $a, string $b): int => strlen($b) <=> strlen($a));
    foreach ($links as $link) {
        $target = realpath($link);
        if ($target === false) {
            throw new RuntimeException('Broken vendor symlink: ' . $link);
        }
        unlink($link);
        if (is_dir($target)) {
            copyTree($target, $link);
        } else {
            copy($target, $link);
        }
    }
}

function copyTree(string $source, string $destination): void
{
    if (!is_dir($destination) && !mkdir($destination, 0700, true) && !is_dir($destination)) {
        throw new RuntimeException('Unable to create copy destination.');
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
    foreach ($iterator as $file) {
        $target = $destination . '/' . substr($file->getPathname(), strlen($source) + 1);
        if ($file->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, $file->getPerms() & 0777, true);
            }
        } else {
            copy($file->getPathname(), $target);
            chmod($target, $file->getPerms() & 0777);
        }
    }
}

function countFiles(string $root): int
{
    $count = 0;
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $count++;
        }
    }
    return $count;
}

function removeTree(string $path): void
{
    if (!is_dir($path)) {
        return;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
    foreach ($iterator as $file) {
        $file->isDir() && !$file->isLink() ? rmdir($file->getPathname()) : unlink($file->getPathname());
    }
    rmdir($path);
}

/** @param list<string> $command @param array<string,string>|null $environment */
function run(array $command, ?string $cwd = null, bool $capture = false, ?array $environment = null): string
{
    $descriptor = [0 => ['file', '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
    $process = proc_open($command, $descriptor, $pipes, $cwd, $environment === null ? null : array_merge(is_array(getenv()) ? getenv() : [], $environment));
    if (!is_resource($process)) {
        throw new RuntimeException('Unable to start command: ' . implode(' ', $command));
    }
    $stdout = stream_get_contents($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);
    $status = proc_close($process);
    if (!$capture && $stdout !== '') {
        echo $stdout;
    }
    if ($stderr !== '') {
        fwrite(STDERR, $stderr);
    }
    if ($status !== 0) {
        throw new RuntimeException('Command failed (' . $status . '): ' . implode(' ', $command));
    }
    return $stdout;
}
