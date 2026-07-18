# Phase 1.37h.1 — Package testsuite discovery

## Goal

Keep extracted module tests in the root verification suite after modules leave `app/`.

## Scope

- Add tool to insert `packages/*/tests` into phpunit.xml.
- Add verifier for package test discovery.
- Add regression tests for test configuration expectations.
- Document the test discovery policy for extracted modules.

## Out of scope

- Vendor package test discovery.
- Moving additional modules.
- Editor.js media integration.
