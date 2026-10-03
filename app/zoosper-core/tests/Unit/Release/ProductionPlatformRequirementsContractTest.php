<?php
declare(strict_types=1);
it('enforces the assembled production runtime platform contract', function (): void {
    $root = dirname(__DIR__, 5);
    $composer = json_decode((string) file_get_contents($root . '/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($composer['config']['platform-check'] ?? null)->toBeTrue()
        ->and($composer['require'])->toHaveKeys(['ext-pdo', 'ext-pdo_mysql', 'ext-curl', 'ext-gd', 'ext-mbstring']);
});
it('verifies platform requirements in CI and the production artifact', function (): void {
    $root = dirname(__DIR__, 5);
    $workflow = (string) file_get_contents($root . '/.github/workflows/quality-gate.yml');
    $artifact = (string) file_get_contents($root . '/tools/build-production-artifact.php');
    expect($workflow)->toContain('extensions: curl, gd, mbstring, pdo_sqlite, pdo_mysql')
        ->toContain('composer check-platform-reqs --no-dev')
        ->and(substr_count($artifact, "'check-platform-reqs'"))->toBe(2)
        ->and(substr_count($artifact, "'--no-dev'"))->toBeGreaterThanOrEqual(3);
});
it('keeps capability requirements with their owning first-party packages', function (): void {
    $root = dirname(__DIR__, 5);
    $media = json_decode((string) file_get_contents($root . '/packages/zoosper-media/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $auth = json_decode((string) file_get_contents($root . '/app/zoosper-auth/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    $twoFactor = json_decode((string) file_get_contents($root . '/app/zoosper-two-factor/composer.json'), true, 512, JSON_THROW_ON_ERROR);
    expect($media['require'])->toHaveKeys(['ext-gd', 'ext-mbstring'])
        ->and($auth['require'])->toHaveKey('ext-mbstring')
        ->and($twoFactor['require'])->toHaveKey('ext-mbstring');
});
