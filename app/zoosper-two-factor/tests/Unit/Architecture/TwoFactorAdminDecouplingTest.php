<?php

declare(strict_types=1);

use Zoosper\Admin\Audit\AuditLogger;
use Zoosper\Admin\Audit\LoginHistoryRepository;
use Zoosper\Admin\Layout\AdminLayout;
use Zoosper\Auth\Layout\AdminLayoutRendererInterface;
use Zoosper\Core\Audit\AuditLoggerInterface;
use Zoosper\Core\Audit\LoginHistoryRecorderInterface;
use Zoosper\TwoFactor\Controller\AdminTwoFactorChallengeController;
use Zoosper\TwoFactor\Controller\AdminTwoFactorSetupController;
use Zoosper\TwoFactor\Service\AdminTwoFactorResetService;

/**
 * Phase 1.41 (dependency graph correction) — proves the zoosper-two-factor
 * module no longer has any direct dependency on zoosper-admin's concrete
 * classes, while Admin's real classes correctly fulfil the new interfaces.
 *
 * HOTFIX: AdminLayoutRendererInterface now imported from Zoosper\Auth\Layout
 * instead of Zoosper\Core\Layout — see that interface's own docblock for why.
 *
 * File placement: app/zoosper-two-factor/tests/Unit/Architecture/TwoFactorAdminDecouplingTest.php
 * — 5 levels up to repo root, matching other per-module tests in this repo.
 */
it('contains zero direct "use Zoosper\\Admin\\" imports anywhere in zoosper-two-factor/src', function (): void {
    $basePath = dirname(__DIR__, 5);
    $srcPath = $basePath . '/app/zoosper-two-factor/src';

    $offendingFiles = [];
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcPath, FilesystemIterator::SKIP_DOTS));

    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $contents = file_get_contents($file->getPathname());
        if ($contents !== false && preg_match('/use\s+Zoosper\\\\Admin\\\\/', $contents) === 1) {
            $offendingFiles[] = str_replace($basePath . '/', '', $file->getPathname());
        }
    }

    expect($offendingFiles)->toBe([], 'Found direct Zoosper\\Admin\\ imports in: ' . implode(', ', $offendingFiles));
});

it('confirms two-factor composer.json no longer requires zoosper/admin', function (): void {
    $basePath = dirname(__DIR__, 5);
    $composerJson = json_decode(
        (string) file_get_contents($basePath . '/app/zoosper-two-factor/composer.json'),
        true
    );

    expect($composerJson['require'])->not->toHaveKey('zoosper/admin');
    expect($composerJson['require'])->toHaveKey('zoosper/core');
    expect($composerJson['require'])->toHaveKey('zoosper/auth');
});

/** @return ReflectionParameter|null */
function phase141FindConstructorParam(string $class, string $paramName): ?ReflectionParameter
{
    $constructor = (new ReflectionClass($class))->getConstructor();
    if ($constructor === null) {
        return null;
    }

    foreach ($constructor->getParameters() as $parameter) {
        if ($parameter->getName() === $paramName) {
            return $parameter;
        }
    }

    return null;
}

it('confirms AdminTwoFactorResetService depends on AuditLoggerInterface, not the concrete AuditLogger', function (): void {
    $param = phase141FindConstructorParam(AdminTwoFactorResetService::class, 'auditLogger');

    expect($param)->not->toBeNull();
    expect((string) $param->getType())->toContain(AuditLoggerInterface::class);
});

it('confirms AdminTwoFactorChallengeController depends on LoginHistoryRecorderInterface, not the concrete LoginHistoryRepository', function (): void {
    $param = phase141FindConstructorParam(AdminTwoFactorChallengeController::class, 'loginHistory');

    expect($param)->not->toBeNull();
    expect((string) $param->getType())->toContain(LoginHistoryRecorderInterface::class);
});

it('confirms AdminTwoFactorSetupController depends on AdminLayoutRendererInterface, not the concrete AdminLayout', function (): void {
    $param = phase141FindConstructorParam(AdminTwoFactorSetupController::class, 'layout');

    expect($param)->not->toBeNull();
    expect((string) $param->getType())->toContain(AdminLayoutRendererInterface::class);
});

it('confirms Admin\'s concrete classes correctly implement the new interfaces', function (): void {
    expect(is_subclass_of(AuditLogger::class, AuditLoggerInterface::class))->toBeTrue();
    expect(is_subclass_of(LoginHistoryRepository::class, LoginHistoryRecorderInterface::class))->toBeTrue();
    expect(is_subclass_of(AdminLayout::class, AdminLayoutRendererInterface::class))->toBeTrue();
});
