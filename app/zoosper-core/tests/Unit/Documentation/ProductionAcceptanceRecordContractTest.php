<?php

declare(strict_types=1);

it('publishes a secret-free target-environment production acceptance record', function (): void {
    $root = dirname(__DIR__, 5);
    $path = $root . '/docs/operations/production-acceptance-record.md';
    $record = (string) file_get_contents($path);
    $runbook = (string) file_get_contents($root . '/docs/operations/production-operator-runbook.md');
    $checklist = (string) file_get_contents($root . '/docs/release-checklist.md');
    $index = (string) file_get_contents($root . '/docs/README.md');
    $builder = (string) file_get_contents($root . '/docs-site/build.php');

    expect($path)->toBeFile()
        ->and($record)->toContain('# Production acceptance record')
        ->toContain('Exact Git commit or verified artifact SHA-256')
        ->toContain('schema:foreign-keys:status --format=json')
        ->toContain('media:queue:status --format=json')
        ->toContain('Content-Security-Policy')
        ->toContain('trusted proxies')
        ->toContain('SMTP endpoint is reachable')
        ->toContain('Queue worker is supervised')
        ->toContain('rate-limit:prune')
        ->toContain('Acceptance decision: `ACCEPTED` / `REJECTED`')
        ->toContain('Do not mark an unverified item as passed')
        ->toContain('Repository-local tests and static contracts do not replace target-environment evidence')
        ->not->toContain('SMTP_PASSWORD=')
        ->not->toContain('DB_PASSWORD=')
        ->and($runbook)->toContain('[production acceptance record](production-acceptance-record.md)')
        ->and($checklist)->toContain('[production acceptance record](operations/production-acceptance-record.md)')
        ->and($index)->toContain('operations/production-acceptance-record.md')
        ->and($builder)->toContain('operations/production-acceptance-record');
});
