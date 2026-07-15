<?php

declare(strict_types=1);

/**
 * Pest bootstrap for the zoosper-auth module's co-located tests.
 *
 * Each module owns its own tests/ folder and Pest.php binding. Built-in
 * expectations only (custom expect()->extend() is not guaranteed to load across
 * co-located module Pest.php files).
 *
 * PCI-aware: never echo or persist secrets, tokens or password hashes in tests.
 */

uses(\Zoosper\Core\Testing\TestCase::class)->in(__DIR__);
