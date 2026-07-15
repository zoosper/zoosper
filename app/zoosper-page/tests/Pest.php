<?php

declare(strict_types=1);

/**
 * Pest bootstrap for the zoosper-page module's co-located tests.
 *
 * Phase 1.21 - co-located edition. Reuses the shared
 * {@see \Zoosper\Core\Testing\TestCase} so page-module tests get the same
 * isolated container without duplicating the base class. The zoosper-page
 * module stays self-contained: its tests live here and travel with the folder.
 */

uses(\Zoosper\Core\Testing\TestCase::class)->in(__DIR__);
