# Launch Readiness: Admin Navigation

Phase 1.37u starts the Launch Readiness Arc by making admin navigation a tested contract.

## Principle

Core CMS navigation must not point to permanent `href="#"` placeholders.

For the launch-readiness phase, the critical targets are:

```text
/admin/sites
/admin/site-domains
/admin/settings
```

If a feature is not fully implemented yet, it should still have a safe route or documented readiness stub so the admin does not look broken.

## Durable tooling

```text
tools/audit-admin-launch-readiness-navigation.php
```

The audit is durable operational tooling and should stay in the root `tools/` directory.

Temporary migration/scaffolding helpers should not be committed once their work has been applied. The durable outputs are the audit, docs, tests and launch-readiness stubs.

## Follow-up

Phase 1.37v should implement real Sites and Site Domains CRUD. Phase 1.37w should implement real Settings persistence and UI.
