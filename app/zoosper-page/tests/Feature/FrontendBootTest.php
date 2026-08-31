<?php

declare(strict_types=1);

use Zoosper\Core\Bootstrap\ApplicationFactory;

/*
 * Phase 1.93/1.94 boot-path guard.
 *
 * Boots the real application end-to-end. Already earned its keep by surfacing
 * the MySQL-only PageRepository schema-detection bug on SQLite.
 *
 * Repo root is four levels up from app/zoosper-page/tests/Feature.
 */

function zoosperBasePath(): string
{
    return dirname(__DIR__, 4);
}

/** Load the project's real runtime helpers (env(), etc.) like public/index.php does. */
function zoosperEnsureRuntimeHelpers(): void
{
    if (function_exists('env')) {
        return;
    }

    $autoload = zoosperBasePath() . '/bootstrap/autoload.php';
    if (is_file($autoload)) {
        require_once $autoload;
    }
}

/**
 * Boot the app and immediately restore the error/exception handlers that
 * ErrorHandler::register() installs, so this test leaves global handler state
 * exactly as it found it (avoids PHPUnit "risky" warnings). Only tests that
 * actually boot should call this — tests that don't must not touch handlers.
 */
function zoosperBootAppCleanly(): \Zoosper\Core\Http\Application
{
    zoosperEnsureRuntimeHelpers();

    $_ENV['APP_ENV'] = 'testing';
    putenv('APP_ENV=testing');
    $_ENV['DB_CONNECTION'] = 'sqlite';
    putenv('DB_CONNECTION=sqlite');
    $_ENV['DB_DRIVER'] = 'sqlite';
    putenv('DB_DRIVER=sqlite');
    $_ENV['DB_DATABASE'] = ':memory:';
    putenv('DB_DATABASE=:memory:');

    $app = ApplicationFactory::create(zoosperBasePath());

    // ApplicationFactory installs early + main handlers; undo both so we don't leak global state.
    restore_error_handler();
    restore_exception_handler();
    restore_error_handler();
    restore_exception_handler();

    return $app;
}

it('boots the application without fataling', function (): void {
    $app = zoosperBootAppCleanly();

    expect($app)->toBeInstanceOf(\Zoosper\Core\Http\Application::class);
});

it('does not call the non-existent view() method on the fallback contract', function (): void {
    // Pure reflection check — does NOT boot the app, so it must not touch
    // global error handlers.
    $interface = new ReflectionClass(\Zoosper\Core\Routing\FallbackHandlerInterface::class);

    expect($interface->hasMethod('supports'))->toBeTrue()
        ->and($interface->hasMethod('handle'))->toBeTrue()
        ->and($interface->hasMethod('view'))->toBeFalse();
});










