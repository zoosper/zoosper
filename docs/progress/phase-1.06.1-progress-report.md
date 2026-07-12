# Phase 1.06.1 progress report

## Feature name

Locale UI Login Controller Hotfix.

## Implemented

- Added hotfix apply tool to remove misplaced locale UI block from `LoginController.php`.
- Updated locale UI verifier to reject `name="locale"` in `LoginController.php`.
- Updated diagnostics and verification runner.

## Why

A raw HTML/PHP locale field was inserted into the login controller and caused a PHP parse error.
