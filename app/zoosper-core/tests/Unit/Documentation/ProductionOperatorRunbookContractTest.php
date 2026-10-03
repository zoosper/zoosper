<?php

declare(strict_types=1);

it('publishes the reviewed production operator boundary as canonical documentation', function (): void {
    $root = dirname(__DIR__, 5);
    $path = $root . '/docs/operations/production-operator-runbook.md';
    $runbook = (string) file_get_contents($path);
    $index = (string) file_get_contents($root . '/docs/README.md');
    $deployment = (string) file_get_contents($root . '/docs/deployment.md');
    $checklist = (string) file_get_contents($root . '/docs/release-checklist.md');
    $builder = (string) file_get_contents($root . '/docs-site/build.php');

    expect($path)->toBeFile()
        ->and($runbook)->toContain('# Production operator runbook')
        ->toContain('TRUSTED_PROXIES')
        ->toContain('CACHE_DRIVER=redis')
        ->toContain('CACHE_REDIS_PASSWORD')
        ->toContain('SMTP_HOST')
        ->toContain('APP_URL')
        ->toContain('security:generate-secrets --write')
        ->toContain('security:generate-secrets --check')
        ->toContain('public/` directory')
        ->toContain('SECURITY_CSP_REPORT_ONLY=false')
        ->toContain('schema:foreign-keys:status --format=json')
        ->toContain('schema:foreign-keys:apply --confirm=apply')
        ->toContain('composer audit --locked --no-interaction')
        ->toContain('SECURITY.md')
        ->and($index)->toContain('operations/production-operator-runbook.md')
        ->and($deployment)->toContain('operations/production-operator-runbook.md')
        ->and($checklist)->toContain('operations/production-operator-runbook.md')
        ->and($builder)->toContain("'operations/production-operator-runbook'");
});
