#!/usr/bin/env bash
set -euo pipefail

# PHP 8.5 warns when a migration in the global namespace imports PDO with:
# use PDO;
# PDO is already global, so this import is unnecessary.
sed -i '/^use PDO;$/d' database/migrations/*.php

echo "Removed unnecessary 'use PDO;' imports from migration files."
