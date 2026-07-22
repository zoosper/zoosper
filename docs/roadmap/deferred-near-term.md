# Deferred Near-term Roadmap Items

This file is the durable parking place for important near-term items that are intentionally paused while the Launch Readiness Arc is prioritised.

These items must not be forgotten; they are deferred because they are less critical than making the CMS admin usable for first internal use.

## Deferred while Launch Readiness Arc is active

| Phase | Title | Reason for deferral |
|---|---|---|
| 1.37n.5 | Optional media-gd/media-imagick processor package planning | Media derivative groundwork is sufficient for early CMS use; admin configuration UX is higher priority. |
| 1.38 | RoleAdminController Latte/template migration | Still important for thin-controller/view extraction, but not blocking first CMS configuration usage. |
| 1.39 | DB-backed rate limiting behind RateLimiterInterface | Important security hardening, but follows after Sites, Domains, Settings and launch-readiness dashboard work. |

## Resume criteria

Revisit this list after the Launch Readiness Arc has delivered:

```text
1.37u — Admin sidebar route integrity and launch readiness stubs
1.37v — Sites and site domains admin CRUD
1.37w — Core settings storage and admin settings UI
1.37x — Site theme assignment and frontend theme validation
1.37y — Dashboard launch readiness checklist
```

At that point, promote the deferred items back into the active roadmap sequence.
