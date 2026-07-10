<?php

declare(strict_types=1);

/**
 * No-op migration retained for migration history continuity.
 *
 * Module-owned declarative schemas are now applied directly by
 * `Zoosper\Core\Database\Migrator` after traditional file migrations. Keeping
 * this file as an empty migration lets the migrator record it cleanly without
 * running bridge logic that duplicates the new built-in schema step.
 */

return [];
