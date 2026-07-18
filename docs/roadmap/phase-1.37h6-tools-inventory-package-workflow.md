# Phase 1.37h.6 — Tools inventory package workflow classification

## Goal

Move permanent package/module workflow tools from `REVIEW` to `KEEP_OPS`.

## Scope

- Update `bin/tools-inventory.php` exact KEEP_OPS list.
- Add a regression test guarding the classification.
- Document the package workflow tool policy.

## Outcome

The tools inventory REVIEW bucket should return to zero after the package transition tooling is classified.
