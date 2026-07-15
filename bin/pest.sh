#!/usr/bin/env bash
set -euo pipefail

mkdir -p var/reports

./vendor/bin/pest > var/reports/pest.log 2>&1

echo
echo "========================================"
echo "Latest Pest Result"
echo "========================================"

grep -E "(PASS|FAIL|Tests:|Assertions:|Duration:)" \
    var/reports/pest.log | tail -40
