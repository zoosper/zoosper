#!/usr/bin/env bash
set -euo pipefail
sed -i '/^use PDO;$/d' database/migrations/*.php
echo "Removed unnecessary 'use PDO;' imports from migration files."
