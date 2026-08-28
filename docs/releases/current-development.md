# Current development line

## Version

`0.3.0-alpha.3`

## Current source status

- `v0.3.0-alpha.3` is the current release identity, preserving the published API, security, and package-ownership baseline while adding the completed Media, Admin, module-discovery, and environment-precedence work.
- Media list, detail, derivative, canonical upload, archive, restore, and guarded permanent-delete APIs are complete in the current development line.
- Module-discovery collision handling has been reconciled against current source and remains fail closed.
- The responsive Admin refinement is complete in source at `364414a4878cde36fd89de8583326e4d1ff1f625`: permission-aware Dashboard links, fluid light/dark presentation, package-owned responsive Grid workflows, a sidebar-owned collapse control, module-owned semantic destination identifiers, and text-only non-interactive navigation groups.
- Final accepted verification for that source was `1,550` tests with `11,157` assertions; the standard quality gate passed `3` checks with `0` errors and `0` warnings.
- The Admin refinement was browser-accepted, committed, and pushed. It was not deployed as part of that phase.

## Release closure and next engineering phase

Phase 10AR is complete at `fcbfa4e736a1c25e1f0e97760507fd42b8294c77`. Deployment-provided process/container values are authoritative over `.env`; staging and production retain fail-closed session and rate-limit policy across HTTP and console boot. Release verification passed `1,557` tests / `11,175` assertions and the strict `3`-check quality gate with `0` errors and `0` warnings. Browser acceptance and production-safe console boot passed. The compiled manifest, release check, release-identity commit, and annotated tag remain gated operations.

After that evidence-led security phase, planned Admin follow-ups include an Admin-owned, module-discovered contract for useful permission-filtered dynamic Dashboard widgets; extensible colour-theme registration; the separate Grid column-preference persistence defect; organised multi-layer navigation discovery; and Auth-owned assigned-user search, Grid, and pagination improvements.

Keep documentation, package READMEs, upgrade notes, and architecture decisions current in every phase.
