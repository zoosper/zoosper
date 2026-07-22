<?php

declare(strict_types=1);

use function PHPUnit\Framework\assertFileExists;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertStringContainsString;
use function PHPUnit\Framework\fail;

$repoRootPath = static function (): string {
    $current = __DIR__;

    while ($current !== dirname($current)) {
        if (is_file($current . DIRECTORY_SEPARATOR . 'composer.json') && is_dir($current . DIRECTORY_SEPARATOR . 'app')) {
            return $current;
        }

        $current = dirname($current);
    }

    fail('Unable to locate Zoosper repository root from ' . __DIR__);
};

$rootPath = static function (string $path = '') use ($repoRootPath): string {
    $root = $repoRootPath();

    return $path === '' ? $root : $root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
};

it('documents role admin cutover patch generation', function () use ($rootPath): void {
    assertFileExists($rootPath('docs/development/role-admin-cutover-patch-generation.md'));

    $contents = (string) file_get_contents($rootPath('docs/development/role-admin-cutover-patch-generation.md'));

    assertStringContainsString('RoleAdminController', $contents);
    assertStringContainsString('candidate patch', $contents);
    assertStringContainsString('non-mutating', $contents);
});

it('provides the role admin cutover patch generator', function () use ($rootPath): void {
    assertFileExists($rootPath('tools/generate-role-admin-cutover-patch.php'));

    $source = (string) file_get_contents($rootPath('tools/generate-role-admin-cutover-patch.php'));

    assertStringContainsString('Generate a local candidate patch', $source);
    assertStringContainsString('role-admin-cutover-candidate.patch', $source);
    assertStringContainsString('candidatePatchBrief', $source);
});

it('generates role admin cutover patch artefacts in an isolated output directory', function () use ($rootPath): void {
    $outputDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'zoosper-role-patch-' . bin2hex(random_bytes(6));
    $command = escapeshellarg(PHP_BINARY)
        . ' '
        . escapeshellarg($rootPath('tools/generate-role-admin-cutover-patch.php'))
        . ' --output-dir='
        . escapeshellarg($outputDir);

    exec($command, $output, $exitCode);

    assertSame(0, $exitCode);
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-cutover-candidate.patch');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-cutover-generation.txt');
    assertFileExists($outputDir . DIRECTORY_SEPARATOR . 'role-admin-cutover-generation.log');

    $patch = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-cutover-candidate.patch');
    $log = (string) file_get_contents($outputDir . DIRECTORY_SEPARATOR . 'role-admin-cutover-generation.log');

    assertStringContainsString('Candidate patch brief', $patch);
    assertStringContainsString('No source changes were made', $patch);
    assertStringContainsString('GENERATION_ERRORS 0', $log);
});
