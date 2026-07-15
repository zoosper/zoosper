<?php

declare(strict_types=1);

/**
 * Pest bootstrap for the zoosper-core module's co-located tests.
 *
 * Phase 1.21 - Pest Test Harness Foundation (co-located edition).
 *
 * Each module owns its own tests/ folder AND its own Pest.php binding, so the
 * module remains self-contained (add/remove as one unit). This file binds the
 * shared {@see \Zoosper\Core\Testing\TestCase} to every test under this
 * directory.
 *
 * NOTE: custom expectations (expect()->extend(...)) are intentionally NOT
 * defined in co-located module Pest.php files. With globbed test roots and no
 * root tests/Pest.php, Pest does not guarantee loading every module Pest.php
 * into its expectation registry, so a custom expectation may be undefined at
 * assert time. Prefer built-in expectations such as toBe() (strict, ordered).
 *
 * PCI-aware: test helpers must never echo or persist secrets or tokens.
 */

uses(\Zoosper\Core\Testing\TestCase::class)->in(__DIR__);
