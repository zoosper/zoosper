<?php

declare(strict_types=1);

namespace Zoosper\Core\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Zoosper\Core\Container\ServiceContainer;

/**
 * Shared, isolated base test case for the entire Zoosper CMS.
 *
 * Phase 1.21 - Pest Test Harness Foundation (co-located edition).
 *
 * This base case lives in `app/zoosper-core/src/Testing`, which means it is
 * already covered by the existing PSR-4 autoload rule
 * (`Zoosper\Core\` -> `app/zoosper-core/src/`). No extra autoload wiring is
 * required for the shared base itself - only each module's own `tests/`
 * namespace needs an `autoload-dev` entry.
 *
 * The design goal (per the "testable in isolation" must-have) is that unit
 * tests can resolve services from a minimal {@see ServiceContainer} WITHOUT
 * booting a full HTTP request.
 *
 * PCI-aware: this base class and its subclasses must never log or dump secrets,
 * TOTP secrets, recovery-code plaintext, session/CSRF tokens, SMTP credentials,
 * or payment data - not even in failure output.
 */
abstract class TestCase extends BaseTestCase
{
    /**
     * Minimal service container used for isolated resolution in tests.
     */
    protected ServiceContainer $container;

    /**
     * Boot a fresh, empty container before each test.
     *
     * We deliberately avoid a full application/HTTP bootstrap. Tests register
     * only the specific services they need via {@see self::fakeService()}.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->container = new ServiceContainer();
    }

    /**
     * Resolve a service from the minimal container.
     *
     * @template T of object
     * @param  class-string<T>|string  $id
     * @return T|object
     */
    protected function service(string $id): object
    {
        return $this->container->get($id);
    }

    /**
     * Register a fake/stub service instance for the current test.
     *
     * Uses the real container API ({@see ServiceContainer::set()}).
     */
    protected function fakeService(string $id, object $instance): void
    {
        $this->container->set($id, $instance);
    }
}
