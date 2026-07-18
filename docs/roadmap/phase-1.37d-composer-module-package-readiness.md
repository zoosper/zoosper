# Phase 1.37d.1 — Composer Module Package Readiness Hotfix

## Fix

The first package-readiness audit only recognised `Vendor_Module` names. Existing first-party Zoosper modules mostly use historical kebab names such as `zoosper-page`, so the audit reported them as invalid.

This hotfix adds a shared `ModulePackageIdentity` helper that recognises both naming styles.

## Outcome

- Existing module names do not need to be renamed immediately.
- Package names and namespaces are derived consistently.
- Module autoload synchronisation can discover all current modules with `src/` folders.
- The package-readiness audit remains informative and does not fail the shell flow merely because some modules do not have package manifests yet.
