# Phase 1.37r.2.1 - Media admin upload readiness test hotfix

## Goal

Fix the readiness test for `tools/dump-media-admin-upload-controller-1.37r2.php`.

## Diagnosis

The test asserted that the dump tool source must not contain `.env`. That was too strict because the tool intentionally includes a PCI/safety note saying it does not read `.env`, uploaded media, secrets or table data.

## Implemented

- Kept the assertion that the dump tool documents the PCI/safety note.
- Replaced the broad `not->toContain('.env')` with narrower checks that `.env` is not listed as a quoted target path.
- No runtime behaviour changed.

## Expected result

`MediaAdminUploadControllerMigrationReadinessTest` should pass.
