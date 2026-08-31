<?php

declare(strict_types=1);

namespace Zoosper\Core\Tests\Unit\Security;

test('packages source root is required and forbidden beneath public', function (): void {
    $basePath = dirname(__DIR__, 5);
    $structure = require $basePath . '/config/project_structure.php';

    expect($structure['required_roots'] ?? [])->toContain('packages');
    expect($structure['forbidden_public_roots'] ?? [])->toContain('packages');
});

test('public webroot policy blocks packages source', function (): void {
    $basePath = dirname(__DIR__, 5);
    $policy = require $basePath . '/config/public_webroot.php';

    expect($policy['blocked_roots'] ?? [])->toContain('/packages/');
});

test('nginx hardening explicitly blocks packages source', function (): void {
    $basePath = dirname(__DIR__, 5);
    $nginx = (string) file_get_contents(
        $basePath . '/deploy/nginx/zoosper-public-hardening.conf',
    );

    expect($nginx)->toContain('location ^~ /packages/ { return 404; }');
});










